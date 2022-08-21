<?php
/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14685
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14690
 * @version		0.1.0
 */
use WHMCS\Cron\Task;

class GofasRunGalaxPixStatus extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 2000;
    protected $defaultFrequency = 5;
    protected $defaultDescription = "Verifica atualizações de status das transações Pix GalaxPay.";
    protected $defaultName = "Verifica status Pix";
    protected $systemName = "GofasRunGalaxPixStatus";
    protected $outputs = array("executed" => array("defaultValue" => 0, "identifier" => "executed", "name" => "Pix verificados"));
    protected $icon = "fas fa-gavel";
    protected $successCountIdentifier = "pix.queue";
    protected $successKeyword = "Executed";
    public function __invoke()
    {
        $this->output("executed")->write($this->executeQueuedJobs());
        return $this;
    }
    public function executeQueuedJobs()
    {
        $executedCount = 0;
        foreach (\WHMCS\Scheduling\Jobs\Queue::where("available_at", "<=", \WHMCS\Carbon::now()->toDateTimeString())->get() as $job) {
            $className = $job->class_name;
            $methodName = $job->method_name;
            try {
                $job->delete();
                $job->executeJob();
                $executedCount++;
            } catch (\Exception $e) {
                logActivity("Exception thrown in pix queue execution" . " (" . $className . "::" . $methodName . ")" . " - " . $e->getMessage());
            }
        }
        return $executedCount;
    }
}