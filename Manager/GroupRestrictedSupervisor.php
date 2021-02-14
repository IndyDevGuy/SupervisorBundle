<?php
namespace IndyDevGuy\SupervisorBundle\Manager;

use IndyDevGuy\SupervisorBundle\Library\Process;
use IndyDevGuy\SupervisorBundle\Library\Supervisor;

/**
 * GroupRestrictedSupervisor.
 */
class GroupRestrictedSupervisor extends Supervisor
{
    /**
     * @var array
     */
    protected array $groups;

    /**
     * The constructor.
     *
     * @param string $name
     * @param string $ipAddress The server ip address
     * @param string $username  Default set to null
     * @param string $password  Default set to null
     * @param int    $port      Default set to null
     * @param array  $groups    Groups to limit this supervisor to
     */
    public function __construct(string $name, string $ipAddress, $username = null, $password = null, $port = null, $groups = [])
    {
        $this->groups = array_filter($groups);

        parent::__construct($name, $ipAddress, $username, $password, $port);
    }

    /**
     * getProcesses.
     *
     * @param array $groups Only show processes in these process groups.
     *
     * @return array
     */
    public function getProcesses($groups = []):array
    {
        return parent::getProcesses(empty($groups) ? $this->groups : $groups);
    }

    /**
     * Start all processes listed in the configuration file.
     *
     * @param bool $wait Wait for each process to be fully started
     *
     * @return array result An array containing start statuses
     */
    public function startAllProcesses($wait = true):array
    {
        if (empty($this->groups)) {
            return parent::startAllProcesses($wait);
        }

        $results = [];

        foreach ($this->groups as $group) {
            $results = array_merge($results, parent::startProcessGroup($group, $wait));
        }

        return $results;
    }

    /**
     * Stop all processes listed in the configuration file.
     *
     * @param bool $wait Wait for each process to be fully stopped
     *
     * @return array result An array containing start statuses
     */
    public function stopAllProcesses($wait = true):array
    {
        if (empty($this->groups)) {
            return parent::stopAllProcesses($wait);
        }

        $results = [];

        foreach ($this->groups as $group) {
            $results = array_merge($results, parent::stopProcessGroup($group, $wait));
        }

        return $results;
    }
}