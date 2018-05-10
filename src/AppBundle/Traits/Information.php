<?php
/**
 * Created by PhpStorm.
 * User: ertiz
 * Date: 2.03.2018
 * Time: 17:07
 */

namespace AppBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
trait Information
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=1)
     */
    protected $status='a';

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Type("DateTime<'d.m.Y H:i:s'>")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Type("DateTime<'d.m.Y H:i:s'>")
     */
    protected $updatedAt;

    /**
     * Sets createdAt.
     *
     * @param \Datetime|null $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @param \Datetime|null $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}