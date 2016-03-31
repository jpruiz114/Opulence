<?php
/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2016 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */
namespace Opulence\Authentication\Credentials\Factories;

use DateInterval;
use DateTimeImmutable;
use Opulence\Authentication\IPrincipal;
use Opulence\Authentication\ISubject;
use Opulence\Authentication\Roles\Orm\IRoleRepository;
use Opulence\Authentication\Tokens\JsonWebTokens\SignedJwt;
use Opulence\Authentication\Tokens\Signatures\ISigner;

/**
 * Tests the JWT credential factory
 */
class JwtCredentialFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var JwtCredentialFactory The factory to use in tests */
    private $factory = null;
    /** @var ISigner|\PHPUnit_Framework_MockObject_MockObject The signer to use in tests */
    private $signer = null;
    /** @var IRoleRepository|\PHPUnit_Framework_MockObject_MockObject The role repository to use in tests */
    private $roleRepository = null;
    /** @var ISubject|\PHPUnit_Framework_MockObject_MockObject The subject to use in tests */
    private $subject = null;

    /**
     * Sets up the tests
     */
    public function setUp()
    {
        $this->signer = $this->getMock(ISigner::class);
        $this->signer->expects($this->any())
            ->method("sign")
            ->willReturn("signed");
        $this->roleRepository = $this->getMock(IRoleRepository::class);
        $this->roleRepository->expects($this->any())
            ->method("getRoleNamesForSubject")
            ->with("principalId")
            ->willReturn(["role1", "role2"]);
        $this->subject = $this->getMock(ISubject::class);
        $principal = $this->getMock(IPrincipal::class);
        $principal->expects($this->any())
            ->method("getId")
            ->willReturn("principalId");
        $this->subject->expects($this->any())
            ->method("getPrimaryPrincipal")
            ->willReturn($principal);
        $this->factory = new JwtCredentialFactory(
            $this->signer,
            $this->roleRepository,
            "foo",
            new DateInterval("P0D"),
            new DateInterval("P1Y")
        );
    }

    /**
     * Tests that the claims are added
     */
    public function testClaimsAdded()
    {
        $credential = $this->factory->createCredentialForSubject($this->subject);
        $tokenString = $credential->getValue("token");
        /** @var SignedJwt $signedJwt */
        $signedJwt = SignedJwt::createFromString($tokenString);
        $payload = $signedJwt->getPayload();
        $this->assertEquals("foo", $payload->getIssuer());
        $this->assertEquals("principalId", $payload->getSubject());
        $this->assertEquals((new DateTimeImmutable)->format("Y"), $payload->getValidFrom()->format("Y"));
        $this->assertEquals((new DateTimeImmutable("+1 year"))->format("Y"), $payload->getValidTo()->format("Y"));
        $this->assertEquals(["role1", "role2"], $payload->get("roles"));
    }
}