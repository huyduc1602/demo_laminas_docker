<?php
namespace UploadFile\Task;

class TestUploadListener extends \GrootSwoole\BaseTaskEventListener
{
    /**
     * @param TestUploadTask $task
     *
     * @return void
     * @author  Duchh - 2024/08/04
     * @package UploadFile\Task
     */
    public function invoke(TestUploadTask $task)
    {
        try {

            $data = $task->getArrayValues();

            // sleep(120);
            try {
                $link = $data['file'];
                $file_headers = @get_headers($link);
                if (!$file_headers
                    || $file_headers[0] == 'HTTP/1.1 404 Not Found'
                ) {
                    $this->logMsg(
                        'post_data: ' . json_encode($data, ' - Link 404')
                    );
                } else {
                    $img = file_get_contents($link);
                    file_put_contents(
                        '/var/www/projects/demo_bg/public/uploads' . substr(
                            $link,
                            strrpos($link, '/')
                        ),
                        $img
                    );
                }

                $this->logMsg('post_data: ' . json_encode($data));
            } catch (\Throwable $e) {
                $this->logMsg('error: ' . json_encode($e->getMessage()));
                $this->saveParseError($e);
            }
           
        } catch (\Throwable $e) {
            $this->saveParseError($e);
        }
    }
}