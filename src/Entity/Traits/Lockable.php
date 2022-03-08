<?php

namespace OHMedia\SecurityBundle\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\User;

trait Lockable
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $locked_at;
    
    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    protected $locked_by;
    
    /**
     * Set locked_at
     *
     * @param \DateTime locked_at
     * @return Entity
     */
    public function setLockedAt(DateTime $locked_at = null)
    {
        $this->locked_at = $locked_at;
    
        return $this;
    }
    
    /**
     * Get locked_at
     *
     * @return \DateTime 
     */
    public function getLockedAt()
    {
        return $this->locked_at;
    }
    
    /**
     * Set locked_by
     *
     * @param string
     * @return Entity
     */
    public function setLockedBy($locked_by)
    {
        $this->locked_by = $locked_by;
    
        return $this;
    }
    
    /**
     * Get locked_by
     *
     * @return string
     */
    public function getLockedBy()
    {
        return $this->locked_by;
    }
    
    public function isUserLocked(User $user)
    {
        if ($this->locked_at && $this->getTimeDiff() < $this->lockExpiresAfter()) {
            return $user->getEmail() !== $this->locked_by;
        }
        
        return false;
    }
    
    public function isUnlockable()
    {
        if ($this->locked_at) {
            return ($this->getTimeDiff() >= $this->unlockableAfter());
        }
        
        return true;
    }
    
    /**
     * Returns the time in seconds an entity should be locked
     *
     * @return integer seconds
     */
    public function lockExpiresAfter()
    {
        return 900; // 15 minutes
    }
    
    /**
     * Returns the time in seconds after which
     * an entity's lock becomes unlockable.
     *
     * Should be smaller than the value returned by lockExpiresAfter()
     *
     * @return integer seconds
     */
    public function unlockableAfter()
    {
        return 300; // 5 minutes
    }
    
    /**
     * Get the difference in seconds between now and when the entity was locked
     *
     * Only use if the entity is locked
     */
    final public function getTimeDiff()
    {
        $now = new DateTime();
            
        return $now->getTimestamp() - $this->locked_at->getTimestamp();
    }
}