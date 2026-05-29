<?php

namespace App\Service;

use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User || !$data->getPassword()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $data->setPassword(
            $this->passwordHasher->hashPassword($data, $data->getPassword())
        );

        $data->setRoles(['ROLE_USER']);

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}