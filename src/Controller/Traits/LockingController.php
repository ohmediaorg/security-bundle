<?php

namespace OHMedia\SecurityBundle\Controller\Traits;

use DateTime;
use DateTimeZone;
use OHMedia\SecurityBundle\Entity\Traits\Lockable;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adds locking functionality to the EntityController
 */
trait LockingController
{
    abstract protected function redirectUnlockAction();

    public function unlockAction(Request $request)
    {
        $this->preActionSetup($request, 'unlock');

        $this->unlockEntity();
        $this->em->flush();

        $this->addFlashNotice($this->entityUnlockNoticeMessage());

        return $this->redirectUnlockAction();
    }

    /**
     * Does a variety of lock checking, setting appropriate flash messages.
     * Automatically locks the entity to the current user if possible.
     *
     * @return bool $locked true if the entity is locked to a different user
     */
    protected function doLocking()
    {
        $message = $type = null;
        $locked = false;

        // check if the entity has locking functionality
        if ($this->hasLockable()) {
            // check if the entity is locked to a different user
            if ($this->entity->isUserLocked($this->user)) {
                // show the appropriate message
                if ($this->isUnlockable()) {
                    $type = 'warning';
                    $message = $this->entityUnlockableMessage();
                }
                else {
                    $type = 'warning';
                    $message = $this->entityStillLockedMessage();
                }

                // this entity is locked to someone else
                $locked = true;
            }
            else {
                // lock the entity to the current user
                $this->lockEntityRaw();

                $type = 'notice';
                $message = $this->entityLockedCurrentMessage();
            }
        }
        else if ($this->showNotLockableMessage()) {
            // notify user that entity cannot be locked
            $type = 'warning';
            $message = $this->entityNotLockableMessage();
        }

        // only show locking messages on get requests
        $showMessage = $get_request = $this->request->isMethod('GET');

        // but also show a specific message if a lock
        // was taken before changes could be saved
        if (!$get_request && $locked) {
            $showMessage = true;
            $type = 'error';
            $message = $this->entityLockTakenMessage();
        }

        if ($showMessage && $message) {
            call_user_func_array([$this, 'addFlash' . ucfirst($type)], [$message]);
        }

        return $locked;
    }

    protected function showNotLockableMessage()
    {
        return false;
    }

    protected function hasLockable()
    {
        return in_array(Lockable::class, class_uses($this->entity));
    }

    private function isUnlockable()
    {
        return $this->entity->isUnlockable() || $this->isDeveloper();
    }

    protected function lockEntity()
    {
        $this->entityLock(true);
    }

    protected function unlockEntity()
    {
        $this->entityLock(false);
    }

    private function entityLock($locking = true)
    {
        if ($this->hasLockable()) {
            $locked = $locking ? new DateTime() : null;
            $lockedBy = $locking ? $this->user->getEmail() : null;

            $this->entity
                ->setLockedAt($locked)
                ->setLockedBy($lockedBy)
            ;
        }
    }

    protected function lockEntityRaw()
    {
        $this->entityLockRaw(true);
    }

    protected function unlockEntityRaw()
    {
        $this->entityLockRaw(false);
    }

    private function entityLockRaw($locking = true)
    {
        if (!$this->hasLockable()) {
            return;
        }

        $table = $this->em
            ->getClassMetadata($this->getEntityClass())
            ->getTableName();

        $lockedAt = $locking
            ? (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s')
            : null;

        $lockedBy = $locking
            ? $this->user->getId()
            : null;

        $sql = "UPDATE $table SET locked_at = :lockedAt, locked_by = :lockedBy WHERE id = :id";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute([
            ':lockedAt' => $lockedAt,
            ':lockedBy' => $lockedBy,
            ':id' => $this->entity->getId(),
        ]);
    }

    /**
     * Get the notice message to display in unlockAction()
     */
    protected function entityUnlockNoticeMessage()
    {
        return sprintf('The %s was unlocked successfully!', $this->human_readable);
    }

    /**
     * Get the warning message to display if the entity cannot be locked
     */
    protected function entityUnlockableMessage()
    {
        return sprintf(
            'This %s may now be unlocked, but "%s" may still be working on it. <a href="%s">Click here to unlock.</a>',
            $this->human_readable,
            $this->entity->getLockedBy(),
            $this->generateActionUrl('unlock')
        );
    }

    /**
     * Get the warning message to display if the entity is still locked
     */
    protected function entityStillLockedMessage()
    {
        $locked_for = $this->entity->unlockableAfter() - $this->entity->getTimeDiff();

        $m = $locked_for > 60
            ? floor($locked_for / 60) . 'm'
            : '';
        $s = ($locked_for % 60) . 's';

        $locked_for = "$m$s";

        return sprintf(
            'This %s is locked to "%s" and will be unlockable in %s.',
            $this->human_readable,
            $this->entity->getLockedBy(),
            $locked_for
        );
    }

    /**
     * Get the message to display if the entity was locked to the current user
     */
    protected function entityLockedCurrentMessage()
    {
        $locked = $this->entity->lockExpiresAfter();
        $unlock = $this->entity->unlockableAfter();

        return sprintf(
            'This %s is locked to you for %s minutes and will become unlockable after %s minutes.',
            $this->human_readable,
            $locked/60,
            $unlock/60
        );
    }

    /**
     * Get the error message to display if the lock was taken
     */
    protected function entityLockTakenMessage()
    {
        return sprintf('It appears another user has taken your lock on this %s.', $this->human_readable);
    }

    /**
     * Get the warning message to display if the entity cannot be locked
     */
    protected function entityNotLockableMessage()
    {
        return sprintf('This %s cannot be locked, so make sure no other users are making changes.', $this->human_readable);
    }
}
