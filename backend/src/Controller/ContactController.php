<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

use App\Entity\ContactForm;

class ContactController extends AbstractController
{
    private $em;
    private $validator;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,)
        {
            $this->em = $em;
            $this->validator = $validator;
        }

    #[Route('/contacts', name: 'contacts_list', methods: ['GET'])]
    public function listContacts(SerializerInterface $serializer): JsonResponse
    {
        $contacts = $this->em->getRepository(ContactForm::class)->findAll();

        $jsonContent = $serializer->serialize($contacts, 'json');

        return $this->json([
            'message' => 'List of contacts',
            'contacts' => $jsonContent,
        ]);
    }

    #[Route('/contacts/{id}', name: 'contacts_show', methods: ['GET'])]
    public function showContact(int $id, SerializerInterface $serializer): JsonResponse
    {
        $contact = $this->em->getRepository(ContactForm::class)->find($id);

        if (!$contact) {
            return $this->json(['message' => 'Contact not found'], 404);
        }

        $serializedContact = $serializer->serialize($contact, 'json');

        return $this->json([
            'message' => 'Contact details',
            'contact' => $serializedContact,
        ]);
    }

    #[Route('/contact/create', name: 'create_contact', methods: ['POST'])]
    public function submitForm(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Stwórz nowy obiekt ContactForm i przypisz mu dane z formularza
        $contactForm = new ContactForm();
        $contactForm->setFirstName($data['firstname']);
        $contactForm->setLastName($data['surname']);
        $contactForm->setEmail($data['email']);
        $contactForm->setMessage($data['text']);
        $contactForm->setSubmissionDate(new \DateTime($data['submissionDate']));

        $errors = $this->validator->validate($contactForm);

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        // Zapisz obiekt do bazy danych
        $this->em->persist($contactForm);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Form submitted successfully!',
            'data' => $data,
        ]);
    }
}
