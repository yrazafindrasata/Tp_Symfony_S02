<?php


namespace App\Controller;


use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscriptionController extends AbstractFOSRestController
{
    private $subscriptionRepository;
    public function __construct(SubscriptionRepository $subscriptionRepository,EntityManagerInterface $em )
    {
        $this->subscriptionRepository = $subscriptionRepository ;
        $this->em = $em;
    }
    /**
     * @Rest\Get("/api/subscriptions")
     */
    public function getApiSubscritpion(){
        $subscriptions = $this->subscriptionRepository ->findAll();
        return $this->view($subscriptions);
    }
    /**
     * @Rest\Get("/api/subscriptions/{name}")
     */
    public function getApiAdminSubscritpion(Subscription $subscritpion){
        return $this->view($subscritpion);
    }

    /**
     * @Rest\Post("/api/admin/subscriptions")
     * @ParamConverter("subscription", converter="fos_rest.request_body")
     */
    public function postApiSubscription(Subscription $subscription, ConstraintViolationListInterface $validationErrors)
    {
        $this->em->persist($subscription);

        $errors = array();
        if ($validationErrors ->count() > 0) {
            /** @var ConstraintViolation $constraintViolation */
            foreach ($validationErrors as $constraintViolation ){

                // Returns the violation message. (Ex. This value should not be blank.)
                $message = $constraintViolation ->getMessage();
                // Returns the property path from the root element to the violation.
                $propertyPath = $constraintViolation ->getPropertyPath ();
                $errors[] = ['message' => $message, 'propertyPath' => $propertyPath ];

            }
        }
        if (!empty($errors)) {
            // Throw a 400 Bad Request with all errors messages (Not readable, you can do better)
            throw new BadRequestHttpException(\json_encode( $errors));
        }else{
            $this->em->flush();
            return $this->view($subscription);
        }
    }

    /**
     * @Rest\Patch("/api/admin/subscription/{name}")
     */
    public function patchApiSubscription(Request $request, ValidatorInterface $validator, Subscription $subscription){
        $attributes=[
            'firstname'=>'setFirstname',
            'lastname'=>'setLastname',
            'address'=>'setAddress',
            'country'=>'setCountry',
            'email'=>'setEmail',
        ];
        foreach($attributes as $attributeName => $setterName){
            if($request->get($attributeName)=== null){
                continue;
            }
            $subscription->$setterName($request->request->get($attributeName));
        }

        $validationErrors = $validator->validate($subscription);
        if ($validationErrors ->count() > 0) {
            /** @var ConstraintViolation $constraintViolation */
            foreach ($validationErrors as $constraintViolation ){

                // Returns the violation message. (Ex. This value should not be blank.)
                $message = $constraintViolation ->getMessage();
                // Returns the property path from the root element to the violation. (Ex. lastname)
                $propertyPath = $constraintViolation ->getPropertyPath ();
                $errors[] = ['message' => $message, 'propertyPath' => $propertyPath ];

            }
        }
        if (!empty($errors)) {
            // Throw a 400 Bad Request with all errors messages (Not readable, you can do better)
            throw new BadRequestHttpException(\json_encode( $errors));
        }else{
            $this->em->flush();
            return $this->view($subscription);
        }

    }

    /**
     * @Rest\Delete("/api/admin/users/{email}")
     */
    public function deleteApiSubscription(Subscription $subscription){
        $this->em->remove($subscription);
        $this->em->flush();
        return new Response(null,400);
    }
}