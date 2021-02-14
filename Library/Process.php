<?php
namespace IndyDevGuy\SupervisorBundle\Library;
use Exception;
use Zend\XmlRpc\Client as RpcClient;
use Zend\XmlRpc\Value\Struct;

/**
 * Process
 */
class Process
{
    /**
     * @var string
     */
    protected string $processName;

    /**
     * @var string
     */
    protected string $processGroup;

    /**
     * @var RpcClient
     */
    protected RpcClient $rpcClient;

    /**
     * The constructor
     *
     * @param string $processName  Name of the process (used to retrieve info)
     * @param string $processGroup Name of the process group
     * @param RpcClient $rpcClient
     */
    public function __construct(string $processName, string $processGroup, RpcClient $rpcClient)
    {
        $this->processName = $processName;
        $this->processGroup = $processGroup;
        $this->rpcClient = $rpcClient;
    }

    /**
     * Get process name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->processName;
    }

    /**
     * Get process group
     *
     *  @return string
     */
    public function getGroup(): string
    {
        return $this->processGroup;
    }

    /**
     * Get info about a process
     *
     * @return array result An array containing data about the process
     */
    public function getProcessInfo(): array
    {
        return $this->rpcClient->call('supervisor.getProcessInfo', array($this->processGroup.':'.$this->processName));
    }

    /**
     * Start a process
     *
     *  @param boolean $wait Wait for process to be fully started
     *
     *  @return boolean Always true unless error
     */
    public function startProcess($wait = true): bool
    {
        return $this->rpcClient->call('supervisor.startProcess', array($this->processGroup.':'.$this->processName, $wait));
    }

    /**
     * Stop a process
     *
     *  @param boolean $wait Wait for process to be fully started
     *
     *  @return boolean Always true unless error
     */
    public function stopProcess($wait = true): bool
    {
        return $this->rpcClient->call('supervisor.stopProcess', array($this->processGroup.':'.$this->processName, $wait));
    }

    /**
     * Start all processes in the group
     *
     * @param boolean $wait Wait for each process to be fully started
     *
     * @return struct A structure containing start statuses
     */
    public function startProcessGroup($wait = true): Struct
    {
        return $this->rpcClient->call('supervisor.startProcessGroup', array($this->processGroup, $wait));
    }

    /**
     * Stop all processes in the group
     *
     * @param boolean $wait Wait for each process to be fully started
     *
     * @return boolean Always return true unless error.
     */
    public function stopProcessGroup($wait = true): bool
    {
        return $this->rpcClient->call('supervisor.stopProcessGroup', array($this->processGroup, $wait));
    }

    /**
     * Send a string of chars to the stdin of the process name.
     * If non-7-bit data is sent (unicode), it is encoded to utf-8 before being sent to the process’ stdin.
     * If chars is not a string or is not unicode, raise INCORRECT_PARAMETERS. If the process is not running, raise NOT_RUNNING.
     * If the process’ stdin cannot accept input (e.g. it was closed by the child process), raise NO_FILE.
     *
     * @param string $data The character data to send to the process
     *
     * @return boolean result Always return True unless error
     */
    public function sendProcessStdin(string $data): bool
    {
        return $this->rpcClient->call('supervisor.sendProcessStdin', array($this->processGroup.':'.$this->processName, $data));
    }

    /**
     * @throws Exception
     * @todo
     */
    public function addProcessGroup()
    {
        throw new Exception('Todo');
    }

    /**
     * @throws Exception
     * @todo
     */
    public function removeProcessGroup()
    {
        throw new Exception('Todo');
    }

    /**
     * Read length bytes from name’s stdout log starting at offset
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return string Bytes of log
     */
    public function readProcessStdoutLog(int $offset, int $length): string
    {
        return $this->rpcClient->call('supervisor.readProcessStdoutLog', array($this->processGroup.':'.$this->processName, $offset, $length));
    }

    /**
     * Read length bytes from name’s stderr log starting at offset
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return string Bytes of log
     */
    public function readProcessStderrLog(int $offset, int $length): string
    {
        return $this->rpcClient->call('supervisor.readProcessStderrLog', array($this->processGroup.':'.$this->processName, $offset, $length));
    }

    /**
     * Provides a more efficient way to tail the (stdout) log than readProcessStdoutLog().
     * Use readProcessStdoutLog() to read chunks and tailProcessStdoutLog() to tail.
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return array [string bytes, integer offset, bool overflow]
     */
    public function tailProcessStdoutLog(int $offset, int $length): array
    {
        return $this->rpcClient->call('supervisor.tailProcessStdoutLog', array($this->processGroup.':'.$this->processName, $offset, $length));
    }

    /**
     * Provides a more efficient way to tail the (stderr) log than readProcessStderrLog(). *
     * Use readProcessStderrLog() to read chunks and tailProcessStderrLog() to tail.
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return array result [string bytes, integer offset, bool overflow]
     */
    public function tailProcessStderrLog(int $offset, int $length): array
    {
        return $this->rpcClient->call('supervisor.tailProcessStderrLog', array($this->processGroup.':'.$this->processName, $offset, $length));
    }

    /**
     * Clear processLogs
     *
     * @return boolean result Always True unless error
     */
    public function clearProcessLogs(): bool
    {
        return $this->rpcClient->call('supervisor.clearProcessLogs', array($this->processGroup.':'.$this->processName));
    }
}