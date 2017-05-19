<?php

namespace Drupal\system\Tests\Cache;

use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the 'session.exists' cache context service.
 *
 * @group Cache
 */
class SessionExistsCacheContextTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['session_exists_cache_context_test'];

  /**
   * Tests \Drupal\Core\Cache\Context\SessionExistsCacheContext::getContext().
   */
  public function testCacheContext() {
    $this->dumpHeaders = TRUE;

    // 1. No session (anonymous).
    $this->assertSessionCookieOnClient(FALSE);
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSessionCookieOnClient(FALSE);
    $this->assertRaw('Session does not exist!');
    $this->assertRaw('[session.exists]=0');

    // 2. Session (authenticated).
    $this->assertSessionCookieOnClient(FALSE);
    $this->drupalLogin($this->rootUser);
    $this->assertSessionCookieOnClient(TRUE);
    $this->assertRaw('Session exists!');
    $this->assertRaw('[session.exists]=1');
    $this->drupalLogout();
    $this->assertSessionCookieOnClient(FALSE);
    $this->assertRaw('Session does not exist!');
    $this->assertRaw('[session.exists]=0');

    // 3. Session (anonymous).
    $this->assertSessionCookieOnClient(FALSE);
    $this->drupalGet(Url::fromRoute('<front>', [], ['query' => ['trigger_session' => 1]]));
    $this->assertSessionCookieOnClient(TRUE);
    $this->assertRaw('Session does not exist!');
    $this->assertRaw('[session.exists]=0');
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSessionCookieOnClient(TRUE);
    $this->assertRaw('Session exists!');
    $this->assertRaw('[session.exists]=1');
  }

  /**
   * Asserts whether a session cookie is present on the client or not.
   */
  function assertSessionCookieOnClient($expected_present) {
    $non_deleted_cookies = array_filter($this->cookies, function ($item) { return $item['value'] !== 'deleted'; });
    $this->assertEqual($expected_present, isset($non_deleted_cookies[$this->getSessionName()]), 'Session cookie exists.');
  }

}
