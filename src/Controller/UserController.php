<?php


namespace App\Controller;


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


class UserController extends AbstractFOSRestController
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
     * @Rest\Get("/api/admin/users")
     */
    public function getApiAllUsers(){
        $users = $this->userRepository ->findAll();
        return $this->view($users);
    }
    /**
     * @Rest\Get("/api/admin/users/{email}")
     */
    public function getApiUser(User $user){
        return $this->view($user);
    }

    /**
     * @Rest\Patch("/api/admin/users/{email}")
     */
    public function patchApiAdminUser(Request $request, ValidatorInterface $validator,User $user){
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
            $user->$setterName($request->request->get($attributeName));
        }

        $validationErrors = $validator->validate($user);
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
            return $this->view($user);
        }

    }

    /**
     * @Rest\Delete("/api/admin/users/{email}")
     */
    public function deleteApiUser(User $user, Request $request){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
        $cards= $this->cardRepository ->findByExampleField($user->getId());
        $this->em->remove($cards);
        $this->em->remove($user);
        $this->em->flush();
        return new Response(null,400);
    }

    /**
     * @Rest\Get("/api/users")
     * @Rest\View(serializerGroups={"anonymous"})
     */
    public function getApiUsers(){
        $users = $this->userRepository ->findAll();
        return $this->view($users);
    }

    /**
     * @Rest\Get("/api/profile/user")
     */
    public function getApiProfileUsers(Request $request){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
        return $this->view($user);
    }

    /**
     * @Rest\Patch("/api/profile/user")
     */
    public function patchApiUser(Request $request, ValidatorInterface $validator){
        $tap=$request->headers->get('X-AUTH-TOKEN');
        $user = $this->userRepository ->findOneBySomeField($tap);
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
            $user->$setterName($request->request->get($attributeName));
        }

        $validationErrors = $validator->validate($user);
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
            return $this->view($user);
        }

    }


    /**
     * @Rest\Post("/api/users")
     * @ParamConverter("user", converter="fos_rest.request_body")
     */
    public function postApiUser(User $user, ConstraintViolationListInterface $validationErrors)
    {
        $this->em->persist($user);

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
            return $this->view($user);
        }
    }


}