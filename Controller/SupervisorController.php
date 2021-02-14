<?php
namespace IndyDevGuy\SupervisorBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SupervisorController extends AbstractController
{
    private static array $publicInformations = ['description', 'group', 'name', 'state', 'statename'];
    /**
     * indexAction
     */
    public function indexAction(): Response
    {
        $supervisorManager = $this->get('supervisor.manager');

        return $this->render('IDGSupervisorBundle:Supervisor:list.html.twig', array(
            'supervisors' => $supervisorManager->getSupervisors(),
        ));
    }

    /**
     * startStopProcessAction
     *
     * @param string $start 1 to start, 0 to stop it
     * @param string $key The key to retrieve a Supervisor object
     * @param string $name The name of a process
     * @param string $group The group of a process
     * @param Request $request
     *
     * @return Response represents an HTTP response.
     * @throws Exception
     */
    public function startStopProcessAction(string $start, string $key, string $name, string $group, Request $request): Response
    {
        $supervisor = $this->get('supervisor.manager')->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $success = true;
        $process = $supervisor->getProcessByNameAndGroup($name, $group);
        try {
            if ($start == "1") {
                $success = $process->startProcess();
            } elseif ($start == "0") {
                $success = $process->stopProcess();
            } else {
                $success = false;
            }

        } catch (Exception $e) {
            $success = false;
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('process.stop.error', array(), 'IDGSupervisorBundle')
            );
        }

        if (!$success) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans(
                    ($start == "1" ? 'process.start.error' : 'process.stop.error'),
                    array(),
                    'IDGSupervisorBundle'
                )
            );
        }

        if ($request->isXmlHttpRequest()) {
            $processInfo = $process->getProcessInfo();
            $res = json_encode([
                'success'       => $success,
                'message'       => implode(', ', $this->get('session')->getFlashBag()->get('error', array())),
                'processInfo'   => $processInfo
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * startStopAllProcessesAction
     *
     * @param Request $request
     * @param string $start 1 to start, 0 to stop it
     * @param string $key The key to retrieve a Supervisor object
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function startStopAllProcessesAction(Request $request, string $start, string $key)
    {
        $supervisor = $this->get('supervisor.manager')->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $processesInfo = true;
        if ($start == "1") {
            $processesInfo = $supervisor->startAllProcesses(false);
        } elseif ($start == "0") {
            $processesInfo = $supervisor->stopAllProcesses(false);
        }

        if ($request->isXmlHttpRequest()) {
            $res = json_encode([
                'processesInfo' => $processesInfo
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * showSupervisorLogAction
     *
     * @param string $key The key to retrieve a Supervisor object
     * @return Response
     * @throws Exception
     */
    public function showSupervisorLogAction(string $key): Response
    {
        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $logs = $supervisor->readLog(0, 0);

        return $this->render('IDGSupervisorBundle:Supervisor:showLog.html.twig', array(
            'log' => $logs,
        ));
    }

    /**
     * clearSupervisorLogAction
     *
     * @param string $key The key to retrieve a Supervisor object
     * @return RedirectResponse
     * @throws Exception
     */
    public function clearSupervisorLogAction($key): RedirectResponse
    {
        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        if ($supervisor->clearLog() !== true) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('logs.delete.error', array(), 'IDGSupervisorBundle')
            );
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * showProcessLogAction
     *
     * @param string $key The key to retrieve a Supervisor object
     * @param string $name The name of a process
     * @param string $group The group of a process
     * @return Response
     * @throws Exception
     */
    public function showProcessLogAction(string $key, string $name, string $group): Response
    {
        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $result = $process->tailProcessStdoutLog(0, 1);
        $stdout = $process->tailProcessStdoutLog(0, $result[1]);

        return $this->render('IDGSupervisorBundle:Supervisor:showLog.html.twig', array(
            'log' => $stdout[0],
        ));
    }

    /**
     * showProcessLogErrAction
     *
     * @param string $key The key to retrieve a Supervisor object
     * @param string $name The name of a process
     * @param string $group The group of a process
     * @return Response
     * @throws Exception
     */
    public function showProcessLogErrAction(string $key, string $name, string $group): Response
    {
        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $result = $process->tailProcessStderrLog(0, 1);
        $stderr = $process->tailProcessStderrLog(0, $result[1]);

        return $this->render('IDGSupervisorBundle:Supervisor:showLog.html.twig', array(
            'log' => $stderr[0],
        ));
    }

    /**
     * clearProcessLogAction
     *
     * @param string $key The key to retrieve a Supervisor object
     * @param string $name The name of a process
     * @param string $group The group of a process
     * @return RedirectResponse
     * @throws Exception
     */
    public function clearProcessLogAction(string $key, string $name, string $group): RedirectResponse
    {
        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        if ($process->clearProcessLogs() !== true) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('logs.delete.error', array(), 'IDGSupervisorBundle')
            );
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * showProcessInfoAction
     *
     * @param string $key The key to retrieve a Supervisor object
     * @param string $name The name of a process
     * @param string $group The group of a process
     * @param Request $request
     *
     * @return Response represents an HTTP response.
     * @throws Exception
     */
    public function showProcessInfoAction(string $key, string $name, string $group, Request $request): Response
    {
        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $infos = $process->getProcessInfo();

        if ($request->isXmlHttpRequest()) {
            $processInfo = [];
            foreach (self::$publicInformations as $public) {
                $processInfo[$public] = $infos[$public];
            }

            $res = json_encode([
                'supervisor'    => $key,
                'processInfo'   => $processInfo,
                'controlLink'   => $this->generateUrl('supervisor.process.startStop', [
                    'key'   => $key,
                    'name'  => $name,
                    'group' => $group,
                    'start' => ($infos['state'] == 10 || $infos['state'] == 20 ? '0' : '1')
                ])
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->render('IDGSupervisorBundle:Supervisor:showInformations.html.twig', array(
            'informations' => $infos,
        ));
    }

    /**
     * showProcessAllInfoAction
     *
     * @param string  $key The key to retrieve a Supervisor object
     * @param Request $request
     *
     * @return Response represents an HTTP response.
     * @throws Exception
     */
    public function showProcessInfoAllAction($key, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new Exception('Ajax request expected here');
        }

        $supervisorManager = $this->get('supervisor.manager');
        $supervisor = $supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new Exception('Supervisor not found');
        }

        $processes = $supervisor->getProcesses();
        $processesInfo = [];
        foreach ($processes as $process) {
            $infos = $process->getProcessInfo();
            $processInfo = [];
            foreach (self::$publicInformations as $public) {
                $processInfo[$public] = $infos[$public];
            }

            $processesInfo[$infos['name']] = [
                'supervisor'    => $key,
                'processInfo'   => $processInfo,
                'controlLink'   => $this->generateUrl('supervisor.process.startStop', [
                    'key'   => $key,
                    'name'  => $infos['name'],
                    'group' => $infos['group'],
                    'start' => ($infos['state'] == 10 || $infos['state'] == 20 ? '0' : '1')
                ])
            ];
        }

        $res = json_encode($processesInfo);

        return new Response($res, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
        ]);
    }
}