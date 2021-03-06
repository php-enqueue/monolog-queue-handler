<?php
namespace Enqueue\Monolog;

use Interop\Queue\PsrContext;
use Interop\Queue\PsrDestination;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Formatter\JsonFormatter;

class QueueHandler extends AbstractProcessingHandler
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var PsrDestination
     */
    private $destination;

    /**
     * @param PsrContext $context
     * @param PsrDestination|string    $destination
     * @param int                      $level
     * @param bool                     $bubble       Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(PsrContext $context, $destination = 'log', $level = Logger::DEBUG, $bubble = true)
    {
        $this->context = $context;

        if (false == $destination instanceof PsrDestination) {
            $destination = $this->context->createQueue($destination);
        }

        $this->destination = $destination;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $message = $this->context->createMessage($record["formatted"], [
            'content_type' => 'application/json',
        ]);

        $this->context->createProducer()->send($this->destination, $message);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, false);
    }
}
