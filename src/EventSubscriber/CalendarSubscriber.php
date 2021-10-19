<?php


namespace App\EventSubscriber;


use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Promotion;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use App\Repository\PromotionRepository;
use CalendarBundle\Entity\Event;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class CalendarSubscriber implements EventSubscriberInterface
{
    private $bookingRepository;
    private $router;
    public function __construct(
        PromotionRepository $bookingRepository,
        UrlGeneratorInterface $router
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->router = $router;
    }
    public static function getSubscribedEvents()
    {

        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {

        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        // You may want to make a custom query from your database to fill the calendar

        $bookings = $this->bookingRepository
            ->createQueryBuilder('p')
            ->WHERE ('  p.startsAt  <=  :now  and  p.endsAt  >= :now ')
            ->setParameter('now',new \DateTime('now'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($bookings as $booking) {
            // this create the events with your data (here booking data) to fill calendar
            $bookingEvent = new Event(
                $booking->getName(),
                $booking->getStartsAt(),
                $booking->getEndsAt() // If the end date is null or not defined, a all day event is created.
            );
            /*
            * Add custom options to events
            *
            * For more information see: https://fullcalendar.io/docs/event-object
            * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
            */
            $cars=array('red','blue','AliceBlue','AntiqueWhite','Violet','Yellow','Teal','Tan','SpringGreen','Brown','Crimson','DarkBlue');
$x=$cars[array_rand ($cars)];
            $bookingEvent->setOptions([
                'backgroundColor' => $x,
                'borderColor' => $x,
            ]);
            // finally, add the event to the CalendarEvent to fill the calendar
            $calendar->addEvent($bookingEvent);
        }
    }

}
