<?php
/**
 * Mémorisation de la langue choisie par le visiteur.
 * Freely inspired by BioPHP's project biophp.org
 */
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class LocaleSubscriber
 *
 * Le sélecteur de langue de la page d'accueil ajoute ?_locale=fr à l'URL.
 * On range ce choix en session pour que toutes les pages suivantes le
 * conservent, sans avoir à préfixer les routes du site.
 *
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @var string  langue utilisée tant que le visiteur n'a rien choisi
     */
    private string $defaultLocale;

    /**
     * @var string[]  langues proposées par le sélecteur
     */
    private array $supportedLocales;

    /**
     * @param string    $defaultLocale
     * @param string[]  $supportedLocales
     */
    public function __construct(string $defaultLocale = 'en', array $supportedLocales = ['en', 'fr'])
    {
        $this->defaultLocale    = $defaultLocale;
        $this->supportedLocales = $supportedLocales;
    }

    /**
     * @param  RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $locale = $request->query->get('_locale');

        // une langue inconnue est ignorée : on ne stocke que ce que l'on sait rendre
        if ($locale !== null && in_array($locale, $this->supportedLocales, true)) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
            return;
        }

        $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Une priorité plus élevée passe en premier : 20 nous place avant le
            // LocaleListener de Symfony (16), qui fixe sinon la langue par défaut
            // et ne la reprendrait plus ensuite.
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
