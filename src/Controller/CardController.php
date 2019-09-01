<?php


namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CardController extends AbstractFOSRestController
{
    private $cardRepository;
    private $userRepository;
    public function __construct(CardRepository $cardRepository,EntityManagerInterface $em, UserRepository $userRepository )
    {
        $this->cardRepository = $cardRepository ;
        $this->userRepository = $userRepository ;
        $this->em = $em;
    }
    /**
     * @Rest\Get("/api/admin/cards")
     */
    public function getApiAdminAllCards(){
        $cards = $this->userRepository ->findAll();
        return $this->view($cards);
    }
    /**
     * @Rest\Delete("/api/admin/cards/{creditCardNumber}")
     */
    public function deleteApiAdminUser(Card $card){
        $this->em->remove($card);
        $this->em->flush();
        return new Response(null,400);
    }

    /**
     * @Rest\Patch("/api/admin/cards/{creditCardNumber}")
     */
    public function patchApiAdminCard( ValidatorInterface $validator, Card $card, Request $request){
            $attributes = [
                'name' => 'setName',
                'value' => 'setValue',
                'currencyCode' => 'setCurrencyCode',
                'country' => 'setCountry',
                'creditCardType' => 'setCreditCardType',
            ];
            foreach ($attributes as $attributeName => $setterName) {
                if ($request->get($attributeName) === null) {
                    continue;
                }
                $card->$setterName($request->request->get($attributeName));
            }

            $validationErrors = $validator->validate($card);
            if ($validationErrors->count() > 0) {
                /** @var ConstraintViolation $constraintViolation */
                foreach ($validationErrors as $constraintViolation) {

                    // Returns the violation message. (Ex. This value should not be blank.)
                    $message = $constraintViolation->getMessage();
                    // Returns the property path from the root element to the violation. (Ex. lastname)
                    $propertyPath = $constraintViolation->getPropertyPath();
                    $errors[] = ['message' => $message, 'propertyPath' => $propertyPath];

                }
            }
            if (!empty($errors)) {
                // Throw a 400 Bad Request with all errors messages (Not readable, you can do better)
                throw new BadRequestHttpException(\json_encode($errors));
            } else {
                $this->em->flush();
                return $this->view($card);
            }


    }

    /**
     * @Rest\Get("/api/profile/cards")
     */
    public function getApiProfileCards(Request $request){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
        $cards= $this->cardRepository ->findByExampleField($user->getId());
        return $this->view($cards);
    }

    /**
     * @Rest\Get("/api/profile/card/{creditCardNumber}")
     */
    public function getApiProfileCard(Request $request, Card $card){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
        if($user->getId()==$card->getUser()->getId()) {
            return $this->view($card);
        }else{
            $error="forbidden";
            throw new BadRequestHttpException(\json_encode($error));
        }
    }

    /**
     * @Rest\Patch("/api/profile/cards/{creditCardNumber}")
     */
    public function patchApiCard( ValidatorInterface $validator, Card $card, Request $request){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
        if($user->getId()==$card->getUser()->getId()) {
            $attributes = [
                'name' => 'setName',
                'value' => 'setValue',
                'currencyCode' => 'setCurrencyCode',
                'country' => 'setCountry',
                'creditCardType' => 'setCreditCardType',
            ];
            foreach ($attributes as $attributeName => $setterName) {
                if ($request->get($attributeName) === null) {
                    continue;
                }
                $card->$setterName($request->request->get($attributeName));
            }

            $validationErrors = $validator->validate($card);
            if ($validationErrors->count() > 0) {
                /** @var ConstraintViolation $constraintViolation */
                foreach ($validationErrors as $constraintViolation) {

                    // Returns the violation message. (Ex. This value should not be blank.)
                    $message = $constraintViolation->getMessage();
                    // Returns the property path from the root element to the violation. (Ex. lastname)
                    $propertyPath = $constraintViolation->getPropertyPath();
                    $errors[] = ['message' => $message, 'propertyPath' => $propertyPath];

                }
            }
            if (!empty($errors)) {
                // Throw a 400 Bad Request with all errors messages (Not readable, you can do better)
                throw new BadRequestHttpException(\json_encode($errors));
            } else {
                $this->em->flush();
                return $this->view($card);
            }
        }else{
            $error="forbidden";
            throw new BadRequestHttpException(\json_encode($error));
        }

    }

    /**
     * @Rest\Post("/api/profile/card")
     * @ParamConverter("card", converter="fos_rest.request_body")
     */
    public function postApiCard(Card $card, ConstraintViolationListInterface $validationErrors)
    {
        $this->em->persist($card);

        $errors = array();
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
            return $this->view($card);
        }
    }

    /**
     * @Rest\Delete("/api/cards/{creditCardNumber}")
     */
    public function deleteApiCard(Card $card, Request $request){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
        if($user->getId()==$card->getUser()->getId()) {
            $this->em->remove($card);
            $this->em->flush();
            return new Response(null, 400);
        }else{
            $error="forbidden";
            throw new BadRequestHttpException(\json_encode($error));
        }
    }

}