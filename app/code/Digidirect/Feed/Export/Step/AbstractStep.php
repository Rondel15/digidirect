<?php

namespace Digidirect\Feed\Export\Step;

use Digidirect\Feed\Export\Context;
use Digidirect\Feed\Model\Config;

abstract class AbstractStep
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $length = 1;

    /**
     * @var int
     */
    protected $index = -1;

    /**
     * @var int
     */
    protected $startedAt = null;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Digidirect\Feed\Export\Step\AbstractStep[]
     */
    protected $steps = [];

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        $data = []
    ) {
        $this->context = $context;
        $this->data = $data;
    }

    /**
     * Call method before execute step
     *
     * @return $this
     */
    public function beforeExecute()
    {
        $this->index = 0;
        $this->startedAt = microtime(true);

        if ($this->steps) {
            $this->length = count($this->steps);
        }

        return $this;
    }

    /**
     * Executor fot step
     *
     * @return $this
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        if ($this->steps) {
            foreach ($this->steps as $step) {
                while (!$step->isCompleted()) {
                    $step->execute();

                    if ($step->isCompleted()) {
                        $this->index++;
                    }

                    $this->context->save();

                    if ($this->context->isTimeout()) {
                        break 2;
                    }
                }
            }
        } else {
            $this->index++;
            $this->context->save();
        }

        if ($this->isCompleted()) {
            $this->afterExecute();
        }

        return $this;
    }

    /**
     * Call method after finish step
     *
     * @return $this
     */
    public function afterExecute()
    {
        return $this;
    }

    /**
     * Step name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Step name
     *
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }

    /**
     * Is ready to execute
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->index == -1;
    }

    /**
     * Is completed
     *
     * @return bool
     */
    public function isCompleted()
    {
        if (!$this->context->getFeed()->getRuleIds()) {
            return $this->index >= $this->length - 1;
        }
        return $this->index >= $this->length;
    }

    /**
     * Is processing now?
     *
     * @return bool
     */
    public function isProcessing()
    {
        return !$this->isCompleted() && $this->index >= 0;
    }

    /**
     * Status of step
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->isReady()) {
            return Config::STATUS_READY;
        } elseif ($this->isCompleted()) {
            return Config::STATUS_COMPLETED;
        }

        return Config::STATUS_PROCESSING;
    }

    /**
     * Add new sub-step
     *
     * @param \Digidirect\Feed\Export\Step\AbstractStep $step
     * @return $this
     */
    public function addStep($step)
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
     * List of steps
     *
     * @return \Digidirect\Feed\Export\Step\AbstractStep[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Set data
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;

        $this->context->save();

        return $this;
    }

    /**
     * Get data
     *
     * @param string $key
     * @return array|bool
     */
    public function getData($key = null)
    {
        if ($key == null) {
            return $this->data;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return false;
    }

    /**
     * Current execution index
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Execution length
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Started time
     *
     * @return int
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * To String
     *
     * @return string
     */
    public function toString()
    {
        $string = '';
        if ($this->getName()) {
            $string .= $this->getName() . '...';
        }

        $string .= $this->index . '  / ' . $this->length;

        foreach ($this->steps as $step) {
            $string .= PHP_EOL . '  ' . $step->toString();
        }

        return $string;
    }

    /**
     * Export step to array
     *
     * @return array
     */
    public function toArray()
    {
        $array = [
            'class' => get_class($this),
            'index' => $this->getIndex(),
            'length' => $this->getLength(),
            'startedAt' => $this->getStartedAt(),
            'name' => $this->getName(),
            'data' => $this->getData(),
            'steps' => []
        ];

        foreach ($this->steps as $step) {
            $array['steps'][] = $step->toArray();
        }

        return $array;
    }

    /**
     * Load step from array
     *
     * @param array $data
     * @return $this
     */
    public function fromArray($data)
    {
        $this->index = $data['index'];
        $this->length = $data['length'];
        $this->data = $data['data'];
        $this->setName($data['name']);
        $this->startedAt = $data['startedAt'];

        foreach ($data['steps'] as $stepData) {
            $step = $this->context->getStepFactory()->create($stepData['class'], ['data' => $stepData['data']]);
            $this->steps[] = $step->fromArray($stepData);
        }

        return $this;
    }
}
