<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\JoinColumn;
use AppBundle\Entity\SocialGroup as SocialGroup;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 *
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToMany(targetEntity="SocialGroup")
     * @JoinTable(name="users_socialgroups",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $sgroups;

    public function __construct()
    {
        parent::__construct();
        $this->sgroups = new ArrayCollection();

    }

    public function addSocialGroup(SocialGroup $sg)
    {
        $this->sgroups[] = $sg;
    }
    /**
     * Gets the groups granted to the user.
     *
     * @return Collection
     */
    public function getSocialGroups()
    {
        return $this->sgroups ?: $this->sgroups = new ArrayCollection();
    }
}

