<?php
/**
 * Copyright (C) 2014 David Young
 *
 * Defines the BCrypt cryptographic hasher
 */
namespace RDev\Models\Cryptography;

class BCryptHasher extends Hasher
{
    /** The default cost used by this hasher */
    const DEFAULT_COST = 10;

    /**
     * {@inheritdoc}
     */
    public function generate($unhashedValue, array $options = [], $pepper = "")
    {
        if(!isset($options["cost"]))
        {
            $options["cost"] = self::DEFAULT_COST;
        }

        return parent::generate($unhashedValue, $options, $pepper);
    }

    /**
     * {@inheritdoc}
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        if(!isset($options["cost"]))
        {
            $options["cost"] = self::DEFAULT_COST;
        }

        return parent::needsRehash($hashedValue, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function setHashAlgorithm()
    {
        $this->hashAlgorithm = PASSWORD_BCRYPT;
    }
} 