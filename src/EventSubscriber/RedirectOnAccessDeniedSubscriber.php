<?php

namespace Drupal\opigno_learning_path\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RedirectOnAccessDeniedSubscriber.
 */
class RedirectOnAccessDeniedSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a new ResponseSubscriber instance.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Redirect if 403 and node an event.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The route building event.
   */
  public function redirectOn403(FilterResponseEvent $event) {
    $status_code = $event->getResponse()->getStatusCode();
    $is_anonymous = $this->user->isAnonymous();
    if ($is_anonymous && $status_code == 403) {
      $current_path = \Drupal::service('path.current')->getPath();
      $response = new RedirectResponse("/user/login/?prev_path={$current_path}");

      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['redirectOn403'];
    return $events;
  }

}