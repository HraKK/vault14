<?php
namespace smok\Vault14Bundle\Auth;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use smok\Vault14Bundle\Entity\User;
class OAuthProvider extends OAuthUserProvider
{
    protected $session, $doctrine, $admins;
    public function __construct($session, $doctrine, $service_container)
    {
        $this->session = $session;
        $this->doctrine = $doctrine;
        $this->container = $service_container;
    }
    public function loadUserByUsername($username)
    {
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb->select('u')
            ->from('Vault14Bundle:User', 'u')
            ->where('u.googleId = :gid')
            ->setParameter('gid', $username)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();
        if (count($result)) {
            return $result[0];
        } else {
            return new User();
        }
    }
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        //Data from Google response
        $google_id = $response->getUsername(); /* An ID like: 112259658235204980084 */
        $email = $response->getEmail();
        //set data in session
        $this->session->set('email', $email);
        //Check if this Google user already exists in our app DB
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb->select('u')
            ->from('Vault14Bundle:User', 'u')
            ->where('u.googleId = :gid')
            ->setParameter('gid', $google_id)
            ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();
        //add to database if doesn't exists
        if (!count($result)) {
            $user = new User();
            $user->setUsername($google_id);
            $user->setEmail($email);
            $user->setGoogleId($google_id);
            //Set some wild random pass since its irrelevant, this is Google login
            $factory = $this->container->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword(md5(uniqid()), $user->getSalt());
            $user->setPassword($password);
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
        } else {
            $user = $result[0]; /* return User */
        }
        //set id
        $this->session->set('id', $user->getId());
        return $this->loadUserByUsername($response->getUsername());
    }
    public function supportsClass($class)
    {
        return $class === 'smok\\Vault14Bundle\\Entity\\User';
    }
}