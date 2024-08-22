<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\PS384;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Symfony\Component\Security\Http\AccessToken\ChainAccessTokenExtractor;
use Symfony\Component\Security\Http\AccessToken\FormEncodedBodyExtractor;
use Symfony\Component\Security\Http\AccessToken\HeaderAccessTokenExtractor;
use Symfony\Component\Security\Http\AccessToken\Oidc\OidcTokenHandler;
use Symfony\Component\Security\Http\AccessToken\Oidc\OidcUserInfoTokenHandler;
use Symfony\Component\Security\Http\AccessToken\QueryAccessTokenExtractor;
use Symfony\Component\Security\Http\Authenticator\AccessTokenAuthenticator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('security.access_token_extractor.header', HeaderAccessTokenExtractor::class)
        ->set('security.access_token_extractor.query_string', QueryAccessTokenExtractor::class)
        ->set('security.access_token_extractor.request_body', FormEncodedBodyExtractor::class)

        ->set('security.authenticator.access_token', AccessTokenAuthenticator::class)
            ->abstract()
            ->args([
                abstract_arg('access token handler'),
                abstract_arg('access token extractor'),
                null,
                null,
                null,
                null,
            ])

        ->set('security.authenticator.access_token.chain_extractor', ChainAccessTokenExtractor::class)
            ->abstract()
            ->args([
                abstract_arg('access token extractors'),
            ])

        // OIDC
        ->set('security.access_token_handler.oidc_user_info.http_client', HttpClientInterface::class)
            ->abstract()
            ->factory([service('http_client'), 'withOptions'])
            ->args([abstract_arg('http client options')])

        ->set('security.access_token_handler.oidc_user_info', OidcUserInfoTokenHandler::class)
            ->abstract()
            ->args([
                abstract_arg('http client'),
                service('logger')->nullOnInvalid(),
                abstract_arg('claim'),
            ])

        ->set('security.access_token_handler.oidc', OidcTokenHandler::class)
            ->abstract()
            ->args([
                abstract_arg('signature algorithm'),
                abstract_arg('signature key'),
                abstract_arg('audience'),
                abstract_arg('issuers'),
                'sub',
                service('logger')->nullOnInvalid(),
                service('clock'),
            ])

        ->set('security.access_token_handler.oidc.jwk', JWK::class)
            ->abstract()
            ->deprecate('symfony/security-http', '7.1', 'The "%service_id%" service is deprecated. Please use "security.access_token_handler.oidc.jwkset" instead')
            ->factory([JWK::class, 'createFromJson'])
            ->args([
                abstract_arg('signature key'),
            ])

        ->set('security.access_token_handler.oidc.jwkset', JWKSet::class)
            ->abstract()
            ->factory([JWKSet::class, 'createFromJson'])
            ->args([
                abstract_arg('signature keyset'),
            ])

        ->set('security.access_token_handler.oidc.algorithm_manager_factory', AlgorithmManagerFactory::class)
            ->args([
                tagged_iterator('security.access_token_handler.oidc.signature_algorithm'),
            ])

        ->set('security.access_token_handler.oidc.signature', AlgorithmManager::class)
            ->abstract()
            ->factory([service('security.access_token_handler.oidc.algorithm_manager_factory'), 'create'])
            ->args([
                abstract_arg('signature algorithms'),
            ])

        ->set('security.access_token_handler.oidc.signature.ES256', ES256::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.ES384', ES384::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.ES512', ES512::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.RS256', RS256::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.RS384', RS384::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.RS512', RS512::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.PS256', PS256::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.PS384', PS384::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')

        ->set('security.access_token_handler.oidc.signature.PS512', PS512::class)
            ->tag('security.access_token_handler.oidc.signature_algorithm')
    ;
};
