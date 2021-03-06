<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LocaleController extends AbstractController
{
    #[Route('/locale/{lang<[a-z]{2}>}', name: 'locale-switch')]
    public function index(string $lang = null, Request $request): RedirectResponse
    {
        if($lang === null) {
            throw new AccessDeniedHttpException();
        }
        //Set the requested locale into the session and request
        $request->getSession()->set("_locale", $lang);
        $request->setLocale($lang);

        //Return to previous page
        return new RedirectResponse($request->headers->get("referer"));
    }
}
