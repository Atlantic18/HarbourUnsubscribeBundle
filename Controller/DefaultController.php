<?php

namespace Harbour\UnsubscribeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Coral\CoreBundle\Controller\JsonController;
use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Exception\JsonException;
use Coral\CoreBundle\Exception\AuthenticationException;

use Harbour\UnsubscribeBundle\Entity\Unsubscribe;

/**
 * @Route("/v1/unsubscriber")
 */
class DefaultController extends JsonController
{
    /**
     * @Route("/generate-hash")
     * @Method("POST")
     */
    public function generateHashAction()
    {
        $request = new JsonParser($this->get("request")->getContent(), true);

        $group = $request->getMandatoryParam('group');
        $items = array();

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('HarbourUnsubscribeBundle:Unsubscribe');

        foreach ($request->getMandatoryParam('recipients') as $recipient)
        {
            $unsubscribe = $repository->findOneBy(array(
                'email_group' => $group,
                'email' => $recipient,
                'account' => $this->getAccount()->getId()
            ));

            if(!$unsubscribe)
            {
                $unsubscribe = new Unsubscribe;
                $unsubscribe->setEmail($recipient);
                $unsubscribe->setEmailGroup($group);
                $unsubscribe->setAccount($this->getAccount());
                $unsubscribe->setHash(sha1(rand()));

                $em->persist($unsubscribe);
            }

            $items[$recipient] = $unsubscribe->getHash();
        }

        $em->flush();

        return $this->createListJsonResponse($items);
    }

    /**
     * @Route("/import")
     * @Method("POST")
     */
    public function importAction()
    {
        $request = new JsonParser($this->get("request")->getContent(), true);

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('HarbourUnsubscribeBundle:Unsubscribe');

        foreach($request->getParams() as $email => $group)
        {
            $unsubscribe = $repository->findOneBy(array(
                'email_group' => $group,
                'email' => $email,
                'account' => $this->getAccount()->getId()
            ));

            if(!$unsubscribe)
            {
                $unsubscribe = new Unsubscribe;
                $unsubscribe->setEmail($email);
                $unsubscribe->setEmailGroup($group);
                $unsubscribe->setAccount($this->getAccount());
                $unsubscribe->setHash(sha1(rand()));
                $unsubscribe->setUnsubscribedAt(new \DateTime());

                $em->persist($unsubscribe);
            }
            elseif(!$unsubscribe->getUnsubscribedAt())
            {
                $unsubscribe->setUnsubscribedAt(new \DateTime());

                $em->persist($unsubscribe);
            }
        }
        $em->flush();

        return $this->createSuccessJsonResponse();
    }

    /**
     * @Route("/unsubscribe/{hash}/{ip_address}")
     * @Method("GET")
     */
    public function unsubscribeAction($hash, $ip_address)
    {
        $repository = $this->getDoctrine()->getRepository('HarbourUnsubscribeBundle:Unsubscribe');

        $unsubscribe = $repository->findOneBy(array(
            'hash' => $hash,
            'account' => $this->getAccount()->getId()
        ));

        $this->throwNotFoundExceptionIf(!$unsubscribe, 'Hash not found.');

        if(!$unsubscribe->getUnsubscribedAt())
        {
            $unsubscribe->setUnsubscribedAt(new \DateTime());
            $unsubscribe->setUnsubscribedIp($ip_address);

            $em = $this->getDoctrine()->getManager();
            $em->persist($unsubscribe);
            $em->flush();
        }

        return $this->createSuccessJsonResponse();
    }

    /**
     * @Route("/list/{group}")
     * @Method("GET")
     */
    public function listAction($group)
    {
        $unsubscriptions = $this->getDoctrine()->getManager()->createQuery(
                'SELECT u.email
                FROM HarbourUnsubscribeBundle:Unsubscribe u
                WHERE u.account = :account_id
                AND u.unsubscribed_at IS NOT NULL
                AND u.email_group = :group'
            )
            ->setParameter('account_id', $this->getAccount()->getId())
            ->setParameter('group', $group)
            ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $items = array();
        foreach ($unsubscriptions as $unsubscription) {
            $items[] = $unsubscription['email'];
        }

        return $this->createListJsonResponse($items);
    }

    /**
     * @Route("/status")
     * @Method("GET")
     */
    public function statusAction()
    {
        $unsubscriptions = $this->getDoctrine()->getManager()->createQuery(
                'SELECT u.email_group, COUNT(u.id) AS unsubscribe_count
                FROM HarbourUnsubscribeBundle:Unsubscribe u
                WHERE u.account = :account_id
                AND u.unsubscribed_at IS NOT NULL
                GROUP BY u.email_group'
            )
            ->setParameter('account_id', $this->getAccount()->getId())
            ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $items = array();
        foreach ($unsubscriptions as $unsubscription) {
            $items[$unsubscription['group']] = $unsubscription['unsubscribe_count'];
        }

        return $this->createListJsonResponse($items);
    }
}
