<?php
namespace App\Library\Repository;
use Illuminate\Http\Request;
use App\Library\Repository\Interfaces\ILogRepository;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Yajra\DataTables\Html\Builder;
use FFM;
class LogRepository implements ILogRepository
{
    public function __construct() {
        $this->request = app('request');
    }
    public function iIndex(Request $request) {
        if ($this->request->input('log')) {
            LogViewer::setFile(base64_decode($this->request->input('log')));
        }
        $logs         = LogViewer::all();
        $files        = LogViewer::getFiles(true);
        $current_file = LogViewer::getFileName();
        $return['logs']         = $logs;
        $return['files']        = $files;
        $return['current_file'] = $current_file;
        return $return;
    }
    public function iDownload(Request $request) {
        return LogViewer::pathToLogFile(base64_decode($this->request->input('download')));
    }
    public function iDelete(Request $request) {
        app('files')->delete(LogViewer::pathToLogFile(base64_decode($this->request->input('del'))));
    }
    public function iDeleteAll(Request $request) {
        foreach (LogViewer::getFiles(true) as $file) {
            app('files')->delete(LogViewer::pathToLogFile($file));
        }
    }
}
class LogViewer
{
    private static $file;
    private static $levels_classes = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'danger',
        'critical'  => 'danger',
        'alert'     => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
    ];
    private static $levels_imgs = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'warning',
        'critical'  => 'warning',
        'alert'     => 'warning',
        'emergency' => 'warning',
        'processed' => 'info',
    ];
    private static $log_levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
        'processed',
    ];
    public const MAX_FILE_SIZE = 52428800; // Why? Uh... Sorry
    public static function setFile($file)
    {
        $file = self::pathToLogFile($file);
        if (app('files')->exists($file)) {
            self::$file = $file;
        }
    }
    public static function pathToLogFile($file)
    {
        $logsPath = storage_path('logs');
        if (app('files')->exists($file)) { // try the absolute path
            return $file;
        }
        $file = $logsPath.'/'.$file;
        // check if requested file is really in the logs directory
        if (dirname($file) !== $logsPath) {
            throw new \Exception('No such log file');
        }
        return $file;
    }
    public static function getFileName()
    {
        return basename(self::$file);
    }
    public static function all()
    {
        $log = [];
        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/';
        if (!self::$file) {
            $log_file = self::getFiles();
            if (!count($log_file)) {
                return [];
            }
            self::$file = $log_file[0];
        }
        if (app('files')->size(self::$file) > self::MAX_FILE_SIZE) {
            return;
        }
        $file = app('files')->get(self::$file);
        preg_match_all($pattern, $file, $headings);
        if (!is_array($headings)) {
            return $log;
        }
        $log_data = preg_split($pattern, $file);
        if ($log_data[0] < 1) {
            array_shift($log_data);
        }
        foreach ($headings as $h) {
            for ($i = 0, $j = count($h); $i < $j; $i++) {
                foreach (self::$log_levels as $level) {
                    if (strpos(strtolower($h[$i]), '.'.$level) || strpos(strtolower($h[$i]), $level.':')) {
                        preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](?:.*?(\w+)\.|.*?)'.$level.': (.*?)( in .*?:[0-9]+)?$/i', $h[$i], $current);
                        if (!isset($current[3])) {
                            continue;
                        }
                        $log[] = [
                            'environment' => $current[2],
                            'level'       => $level,
                            'level_class' => self::$levels_classes[$level],
                            'level_img'   => self::$levels_imgs[$level],
                            'date'        => $current[1],
                            'text'        => $current[3],
                            'in_file'     => $current[4] ?? null,
                            'stack'       => preg_replace("/^\n*/", '', $log_data[$i]),
                        ];
                    }
                }
            }
        }
        return array_reverse($log);
    }
    public static function getFiles($basename = false)
    {
        $files = glob(storage_path().'/logs/*.log');
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $files[$k] = basename($file);
            }
        }
        return array_values($files);
    }
}
