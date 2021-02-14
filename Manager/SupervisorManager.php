<?php
namespace IndyDevGuy\SupervisorBundle\Manager;

use IndyDevGuy\SupervisorBundle\Library\Supervisor;

class SupervisorManager
{
    /**
     * @var array
     */
    private array $supervisors = [];

    /**
     * Constructor
     *
     * @param array $supervisorsConfiguration Configuration in the symfony parameters
     */
    public function __construct(array $supervisorsConfiguration)
    {
        foreach ($supervisorsConfiguration as $serverName => $configuration) {
            $supervisor = new GroupRestrictedSupervisor(
                $serverName,
                $configuration['host'],
                $configuration['username'],
                $configuration['password'],
                $configuration['port'],
                $configuration['groups']
            );
            $this->supervisors[$supervisor->getKey()] = $supervisor;
        }
    }

    /**
     * Get all supervisors
     *
     * @return Supervisor[]
     */
    public function getSupervisors(): array
    {
        return $this->supervisors;
    }

    /**
     * Get Supervisor by key
     *
     * @param string $key
     *
     * @return Supervisor|null
     */
    public function getSupervisorByKey(string $key): ?Supervisor
    {
        if (isset($this->supervisors[$key])) {
            return $this->supervisors[$key];
        }

        return null;
    }
}