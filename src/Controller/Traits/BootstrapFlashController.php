<?php

namespace OHMedia\SecurityBundle\Controller\Traits;

trait BootstrapFlashController
{
    protected function addFlashPrimary(string $message)
    {
        $this->addFlash('primary', $message);
    }

    protected function addFlashSecondary(string $message)
    {
        $this->addFlash('secondary', $message);
    }

    protected function addFlashSuccess(string $message)
    {
        $this->addFlash('success', $message);
    }

    protected function addFlashDanger(string $message)
    {
        $this->addFlash('danger', $message);
    }

    protected function addFlashWarning(string $message)
    {
        $this->addFlash('warning', $message);
    }

    protected function addFlashInfo(string $message)
    {
        $this->addFlash('info', $message);
    }

    protected function addFlashLight(string $message)
    {
        $this->addFlash('light', $message);
    }

    protected function addFlashDark(string $message)
    {
        $this->addFlash('dark', $message);
    }
}
