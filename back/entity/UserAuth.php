<?php



namespace Entity;

/**
 * UserAuth
 *
 * @Table(name="user_auth")
 * @Entity
 */
class UserAuth
{
    /**
     * @var integer
     *
     * @Column(name="id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;

    /**
     * @var string
     *
     * @Column(name="token", type="string", length=45, nullable=false)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @Column(name="exp", type="datetime", nullable=false)
     */
    private $exp;

    /**
     * @var \DateTime
     *
     * @Column(name="dt", type="datetime", nullable=false)
     */
    private $dt;


}