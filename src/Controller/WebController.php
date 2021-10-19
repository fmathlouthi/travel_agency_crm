<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Middleware\ApiAi;


class WebController extends AbstractController {

    /**
     * @Route("/message", name="message")
     */
    function messageAction(Request $request, \App\Services\BotService $botService)
    {

		
        // Create a BotMan instance, using the WebDriver
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);
        $botman = BotManFactory::create([]); //No config options required

        //Setup DialogFlow middleware
        $dialogflow = ApiAi::create($this->getParameter('DIALOGFLOW_TOKEN'))->listenForAction();
        $botman->middleware->received($dialogflow);

        // Give the bot some things to listen for.
        $botman->hears('(hello|hi|hey)', function (BotMan $bot) use ($botService) {
            $bot->reply($botService->handleHello().' dear '.$this->getUser()->getProfile()->getFirstname());
        });

        $botman->hears('(what night|when) is club night.*', function (BotMan $bot) use ($botService) {
            $bot->reply($botService->handleClubNights());
        });

        $botman->hears('_THISWEEK_', function (Botman $bot) use ($botService) {
            $bot->reply($botService->handleThisWeeksActivities());
        })->middleware($dialogflow);

        $botman->hears('_ENROLMENT_', function (Botman $bot) use ($botService) {
            //$extras = $bot->getMessage()->getExtras();
            $bot->reply($botService->handleEnrolment());
        })->middleware($dialogflow);

        $botman->hears('_INSURANCE_', function (Botman $bot) use ($botService) {
            $bot->reply($botService->handleInsurance());
        })->middleware($dialogflow);

        $botman->hears('_MEMBERSHIP_', function (Botman $bot) use ($botService) {
            $bot->reply($botService->handleMembership());
        })->middleware($dialogflow);
// Give the bot something to listen for.
        $botman->hears('hello', function (BotMan $bot) {
            $bot->reply('Hello yourself.');
        });
        $botman->hears('hello1', function (BotMan $bot) {
            $bot->reply('Hello yourself.');
        });
        $botman->hears('call me {name}', function ($bot, $name) {
            $bot->reply('Your name is: '.$name);
        });
        $botman->hears('call me {name} the {adjective}', function ($bot, $name, $adjective) {
            $bot->reply('Hello '.$name.'. You truly are '.$adjective);
        });
        $botman->hears('I want ([0-9]+)', function ($bot, $number) {
            $bot->reply('You will get: '.$number);
        });
        $botman->hears('I want ([0-9]+) portions of (Cheese|Cake)', function ($bot, $amount, $dish) {
            $bot->reply('You will get '.$amount.' portions of '.$dish.' served shortly.');
        });
        
		
		$botman->fallback(function($bot) {
    $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ... ');
});
        // Start listening
        $botman->listen();

        //Send an empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }



}
