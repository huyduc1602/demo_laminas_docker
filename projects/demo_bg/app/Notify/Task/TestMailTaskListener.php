<?php

namespace Notify\Task;

use Models\Utilities\AppUtilities;
use Zf\Ext\Utilities\ZFTransportSmtp;

/**
 * @author PhapIT
 * @since 2023/12/27
 */
class TestMailTaskListener extends \GrootSwoole\BaseTaskEventListener
{

    /**
     * @param TestMailTask $task
     *
     * @throws \Throwable
     * @author PhapIT
     * @since 2023/12/27
     */
    public function invoke(TestMailTask $task)
    {
        try {
            $data = $task->getArrayValues();

            sleep(120);

            $this->logMsg('post_data: ' . json_encode($data));
        } catch (\Throwable $e) {
            $this->saveParseError($e);
        }
    }
}
