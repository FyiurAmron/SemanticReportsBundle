<?php

namespace Eidsonator\SemanticReportsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Eidsonator\SemanticReportsBundle\lib\FileSystemCache\lib\FileSystemCache;
use Eidsonator\SemanticReportsBundle\lib\simplediff\SimpleDiff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eidsonator\SemanticReportsBundle\Classes\Headers\VariableHeader;

use AppBundle\RmSchema;

class DefaultController extends Controller
{
    private $reportDirectory;
    private $defaultFileExtensionMapping;

    public function getUserRole () {
        return $this->getUser()->getRoles()[0];
    }

    public function getUserDb () {
        $user = $this->getUser();
        if ( $user === null ) {
            return new User();
        }
        $username = $this->getUser()->getUsername();

        return RmSchema::getUserByName( $this->getDoctrine()->getManager(), $username );
    }
    
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->reportDirectory = $this->container->getParameter('reportDirectory');
        $this->defaultFileExtensionMapping = $this->container->getParameter('default_file_extension_mapping');
        if (!defined('REPORT_DIRECTORY')) {
            define('REPORT_DIRECTORY', $this->reportDirectory);
        }
    }
    
    public function copyTwig() {
        return (clone $this)->get('twig');
    }

    public function listReportsAction()
    {
        $errors = array();

        $reports = $this->getReports($this->reportDirectory, $errors);

        $template_vars['reports'] = $reports;
        $template_vars['report_errors'] = $errors;
        $template_vars['recent_reports'] = $this->getRecentReports();
        $start = microtime(true);
        return $this->render('@SemanticReports/Default/html/report_list.twig', $template_vars);
    }

    public function listReportsJsonAction(Request $request)
    {

        $response = new JsonResponse();
        $parts = [];
        $this->generateReportListRecursive($parts);
        $response->setData($parts);
        return $response;
    }

    protected function generateReportListRecursive(&$parts, $reports = null)
    {
        if ($reports === null) {
            $errors = array();
            $reports = $this->getReports($this->reportDirectory, $errors);
        }

        //weight by popular reports
        $recently_run = FileSystemCache::retrieve(FileSystemCache::generateCacheKey('recently_run'));
        $popular = array();
        if ($recently_run !== false) {
            foreach ($recently_run as $report) {
                if (!isset($popular[$report])) {
                    $popular[$report] = 1;
                } else {
                    $popular[$report]++;
                }
            }
        }

        foreach ($reports as $report) {
            if ($report['is_dir'] && $report['children']) {
                //skip if the directory doesn't have a title
                if (!isset($report['Title']) || !$report['Title']) {
                    continue;
                }
                $this->generateReportListRecursive($parts, $report['children']);
            } else {
                //skip if report is marked as dangerous
                if ((isset($report['stop']) && $report['stop']) || isset($report['Caution']) || isset($report['warning'])) {
                    continue;
                }
                //skip if report is marked as ignore
                if (isset($report['ignore']) && $report['ignore']) {
                    continue;
                }
                if (!isset($report['report'])) {
                    continue;
                }
                if (isset($popular[$report['report']])) {
                    $popularity = $popular[$report['report']];
                } else {
                    $popularity = 0;
                }

                $parts[] = [
                    'name'=>$report['Name'],
                    'url'=>$report['url'],
                    'popularity'=>$popularity
                ];
            }
        }
    }

    public function displayHtmlAction(Request $request)
    {
        return $this->display($request, 'Html');
    }

    public function displayFormattedHtmlAction(Request $request)
    {
        return $this->display($request, 'FormattedHtml');
    }

    public function displayXmlAction(Request $request)
    {
        return $this->display($request, 'Xml');
    }

    public function displayChartAction(Request $request)
    {
        return $this->display($request, 'Chart');
    }

    public function displayXlsxAction(Request $request)
    {
        return $this->display($request, 'Xlsx');
    }

    public function displayXlsAction(Request $request)
    {
        return $this->display($request, 'Xls');
    }

    public function displayRawAction(Request $request)
    {
        return $this->display($request, 'Raw');
    }

    public function displayCsvAction(Request $request)
    {
        return $this->display($request, 'Csv');
    }

    public function displayJsonAction(Request $request)
    {
        return $this->display($request, 'Json');
    }
    public function displaySqlAction(Request $request)
    {
        return $this->display($request, 'Sql');
    }
    public function displayTableAction(Request $request)
    {
        return $this->display($request, 'Table');
    }

    public function displayTextAction(Request $request)
    {
        return $this->display($request, 'Text');
    }

    public function displayDebugAction(Request $request)
    {
        return $this->display($request, 'Debug');
    }

    private function display(Request $request, $type)
    {
        $className = "Eidsonator\\SemanticReportsBundle\\Classes\\ReportFormats\\{$type}ReportFormat";
        $error_header = 'An error occurred while running your report';
        $content = '';

        $displayTypeVar = [
          'name' => 'displayType',
          'display' => 'Sposób prezentacji',
          'type' => 'select',
          'options' => [ 'tabela', 'wykres', 'tabela i wykres' ],
          'multiple' => false,
          'default' => 'tabela'
        ];

        $pivotVar = [
          'name' => 'pivot',
          'display' => 'Obróć tabelę',
          'type' => 'select',
          'options' => [ 'nie', 'tak' ],
          'multiple' => false,
          'default' => 'nie'
        ];

        $pivotChartVar = [
          'name' => 'pivotChart',
          'display' => 'Obróć wykres',
          'type' => 'select',
          'options' => [ 'nie', 'tak' ],
          'multiple' => false,
          'default' => 'nie'
        ];

        try {
            if (!class_exists($className)) {
                $error_header = 'Unknown report format';
                throw new \Exception("Unknown report format '$type'");
            }
            try {
            
                $report = new Report($request->query->get('report'), [], null, null, $this->container, $this);

                $report = $className::prepareReport($report);
                
                VariableHeader::init( $displayTypeVar, $report );
                VariableHeader::init( $pivotVar, $report );
                VariableHeader::init( $pivotChartVar, $report );

                $rm = $report->macros;
                $pivot = ( isset( $rm['pivot'] ) && $rm['pivot'] === 'tak' );

                $ro = &$report->options;
                if ( !isset( $ro['Formatting'] ) ) {
                    $ro['Formatting'] = [ [ 'dataset' => true, 'nodata' => false, ] ];
                }
                foreach( $report->options['Formatting'] as &$opt ) {
                    $opt['vertical'] = $pivot;
                }
                
            } catch (\Exception $e) {
                $error_header = 'An error occurred while preparing your report';
                throw $e;
            }
            $report->headers[] = 'Formatting';

            $twigArray = $className::display($report, $request);

            if (is_array($twigArray)) {
                $reportURL =  $this->generateUrl('eidsonator_generate_report');
                $report->setBaseURL($reportURL);
                $twigArray['vars'] = $report->getReportVariables($twigArray['vars']);
                $content = isset($report->options['Query_Formatted']) ? $report->options['Query_Formatted'] : null;
            }


        } catch (\Exception $e) {
            // dump( $e );
            $title = ( isset($report) ) ? $report->report : 'broken';

            return $this->render('@SemanticReports/Default/html/page.twig', [
                'title'=> $title,
                'header'=>'<h2>'.$error_header.'</h2>',
                'error'=>$e->getMessage(),
                'content'=>$content,
                'breadcrumb'=>['Report List' => '', $title => true]
            ]);
        }
        if (isset($twigArray['template'])) {
            return $this->render($twigArray['template'], $twigArray['vars']);
        } else {
            $twigArray = nl2br($twigArray);
            if ($type !== 'Debug') {
                $twigArray = preg_replace('/[ ](?=[^>]*(?:<|$))/', '&nbsp', $twigArray);
            }
            return new Response("{$twigArray}");
        }
    }

    // note: quite buggy ATM
    public function editAction(Request $request)
    {
        $templateVars = array();
        $report = $request->query->get('report');
        $ext = pathinfo($report, PATHINFO_EXTENSION);
        try {
            $report = new Report($report, [], null, false, $this->container, $this);
            $templateVars = [
                'report' => $report,
                'options' => $report->options,
                'contents' => $report->getRaw(),
                'report_querystring' => $_SERVER['QUERY_STRING'],                
                'extension' => $ext
            ];
            $templateVars = $report->getReportVariables($templateVars);
        } catch (\Exception $e) { //if there is an error parsing the report
            // dump( $report );
            $templateVars['error'] = $e;
        }

        if (isset($_POST['preview'])) {
            $diff = new SimpleDiff();
            $html = "<pre>" . $diff->htmlDiffSummary($templateVars['contents'], $_POST['contents']) . "</pre>";
            
            return new Response($html);
        } elseif (isset($_POST['save'])) {
            $html = $report->setReportFileContents($_POST['contents']);
            return new Response($html);
        } else {
            return $this->render('@SemanticReports/Default/html/report_editor.twig', $templateVars);
        }

    }

    protected function getReports($dir, &$errors = null)
    {
        $base = $this->reportDirectory;
        $reports = glob($dir . '*', GLOB_NOSORT);
        $return = [];
        foreach ($reports as $key => $report) {
            $title = $description = false;

            if (is_dir($report)) {
                if (file_exists($report.'/TITLE.txt')) {
                    $title = file_get_contents($report.'/TITLE.txt');
                }
                if (file_exists($report.'/README.txt')) {
                    $description = file_get_contents($report.'/README.txt');
                }

                $id = str_replace(
                    ['_', '-', '/', ' '],
                    ['', '', '_', '-'],
                    trim(substr($report, strlen($base)), '/')
                );

                $children = $this->getReports($report.'/', $errors);

                $count = 0;
                foreach ($children as $child) {
                    if (isset($child['count'])) {
                        $count += $child['count'];
                    } else {
                        $count++;
                    }
                }

                $return[] = array(
                    'Name'=>ucwords(str_replace(array('_','-'), ' ', basename($report))),
                    'Title'=>$title,
                    'Id'=> $id,
                    'Description'=>$description,
                    'is_dir'=>true,
                    'children'=>$children,
                    'count'=>$count
                );
            } else {
                $bn = basename( $report );
                if ( strpos( $bn, '.') === false ) {
                    continue;
                }
                $ur = $this->getUserRole();
                if ( $ur === 'ROLE_CITY_COORD' && strpos( $bn, 'km' ) !== 0 ) {
                    continue;
                }
                if ( $ur === 'ROLE_CAMPAIGN_COORD' && strpos( $bn, 'kk' ) !== 0 ) {
                    continue;
                }
                $ext = pathinfo($report, PATHINFO_EXTENSION);
                if (!isset($this->defaultFileExtensionMapping[$ext])) {
                    continue;
                }

                $name = substr($report, strlen($base));

                try {
                    $data = $this->getReportHeaders($name);
                    $grantedAccess = true;
                    if (isset($data['permission'])) {
                        $grantedAccess = $this->get('security.context')->isGranted($data['permission']);
                    }
                    if ($grantedAccess) {
                        $return[] = $data;
                    }
                } catch (\Exception $e) {
                    // dump( $e );
                    $errors[] = [
                        'report' => $name,
                        'exception' => $e
                    ];
                }
            }
        }

        usort($return, function (&$a, &$b) {
            if ($a['is_dir'] && !$b['is_dir']) {
                return 1;
            } elseif ($b['is_dir'] && !$a['is_dir']) {
                return -1;
            }
            if (!isset($a['Title']) && !isset($b['Title'])) {
                return strcmp($a['Name'], $b['Name']);
            } elseif (!isset($a['Title'])) {
                return 1;
            } elseif (!isset($b['Title'])) {
                return -1;
            }

            return strcmp($a['Title'], $b['Title']);
        });

        return $return;
    }

    protected function getReportHeaders($report)
    {
        $cacheKey = FileSystemCache::generateCacheKey($report, 'report_headers');

        //check if report data is cached and newer than when the report file was created
        //the url parameter ?nocache will bypass this and not use cache
        $data =false;
        if (!isset($_REQUEST['nocache'])) {
            $data = FileSystemCache::retrieve($cacheKey, $this->reportDirectory . $report);
        }

        //report data not cached, need to parse it
        if ($data === false) {
            $temp = new Report($report, array(), null, null, $this->container, $this);

            $data = $temp->options;

            $data['report'] = $report;
            $data['url'] = $this->generateUrl('eidsonator_generate_report', ["report" => $report]); //'report='.$report;
            $data['get'] = $report; //todo generate url
            $data['is_dir'] = false;
            $data['Id'] = str_replace(array('_', '-', '/', ' ', '.'), array('', '', '_', '-', '_'), trim($report, '/'));
            if (!isset($data['Name'])) {
                $data['Name'] = ucwords(str_replace(array('_', '-'), ' ', basename($report)));
            }
            //store parsed report in cache
            FileSystemCache::store($cacheKey, $data);
        }

        return $data;
    }

    public function getRecentReports()
    {
        $recently_run = FileSystemCache::retrieve(FileSystemCache::generateCacheKey('recently_run'));
        $recent = array();
        if ($recently_run !== false) {
            $i = 0;
            foreach ($recently_run as $report) {
                if ($i > 10) {
                    break;
                }
                $headers = $this->getReportHeaders($report);
                if (!$headers) {
                    continue;
                }
                if (isset($headers['url'])) {
                    if (isset($recent[$headers['url']])) {
                        continue;
                    }
                }
                $recent[$headers['url']] = $headers;
                $i++;
            }
        }
        return array_values($recent);
    }
}
