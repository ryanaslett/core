<?php

/**
 * @file
 * Contains \Drupal\system\Tests\RouteProcessor\RouteNoneTest.
 */

namespace Drupal\system\Tests\RouteProcessor;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\simpletest\KernelTestBase;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * @see system.routing.yml
 * @see \Drupal\Core\Routing\UrlGenerator
 * @group route_processor
 */
class RouteNoneTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system'];

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', ['router']);
    \Drupal::service('router.builder')->rebuild();

    $this->urlGenerator = \Drupal::urlGenerator();
  }

  /**
   * Tests the output process.
   */
  public function testProcessOutbound() {
    $expected_cacheability = (new CacheableMetadata())->setCacheMaxAge(Cache::PERMANENT);

    $request_stack = \Drupal::requestStack();
    /** @var \Symfony\Component\Routing\RequestContext $request_context */
    $request_context = \Drupal::service('router.request_context');

    // Test request with subdir on homepage.
    $server = [
      'SCRIPT_NAME' => '/subdir/index.php',
      'SCRIPT_FILENAME' => \Drupal::root() . '/index.php',
      'SERVER_NAME' => 'http://www.example.com',
    ];
    $request = Request::create('/subdir', 'GET', [], [], [], $server);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, '<front>');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route('/'));

    $request_stack->push($request);
    $request_context->fromRequest($request);
    $this->assertEqual(['', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], [], TRUE, TRUE));
    $this->assertEqual(['#test-fragment', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], ['fragment' => 'test-fragment'], TRUE));

    // Test request with subdir on other page.
    $server = [
      'SCRIPT_NAME' => '/subdir/index.php',
      'SCRIPT_FILENAME' => \Drupal::root() . '/index.php',
      'SERVER_NAME' => 'http://www.example.com',
    ];
    $request = Request::create('/subdir/node/add', 'GET', [], [], [], $server);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'node.add');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route('/node/add'));

    $request_stack->push($request);
    $request_context->fromRequest($request);
    $this->assertEqual(['', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], [], TRUE, TRUE));
    $this->assertEqual(['#test-fragment', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], ['fragment' => 'test-fragment'], TRUE));

    // Test request without subdir on the homepage.
    $server = [
      'SCRIPT_NAME' => '/index.php',
      'SCRIPT_FILENAME' => \Drupal::root() . '/index.php',
      'SERVER_NAME' => 'http://www.example.com',
    ];
    $request = Request::create('/', 'GET', [], [], [], $server);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, '<front>');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route('/'));

    $request_stack->push($request);
    $request_context->fromRequest($request);
    $this->assertEqual(['', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], [], TRUE, TRUE));
    $this->assertEqual(['#test-fragment', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], ['fragment' => 'test-fragment'], TRUE));

    // Test request without subdir on other page.
    $server = [
      'SCRIPT_NAME' => '/index.php',
      'SCRIPT_FILENAME' => \Drupal::root() . '/index.php',
      'SERVER_NAME' => 'http://www.example.com',
    ];
    $request = Request::create('/node/add', 'GET', [], [], [], $server);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'node.add');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route('/node/add'));

    $request_stack->push($request);
    $request_context->fromRequest($request);
    $this->assertEqual(['', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], [], TRUE, TRUE));
    $this->assertEqual(['#test-fragment', $expected_cacheability], $this->urlGenerator->generateFromRoute('<none>', [], ['fragment' => 'test-fragment'], TRUE));
  }

}
