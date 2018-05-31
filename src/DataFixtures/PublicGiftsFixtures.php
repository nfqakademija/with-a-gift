<?php

namespace App\DataFixtures;

use App\Entity\Gift;
use App\Entity\GiftList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;


class PublicGiftsFixtures extends Fixture
{

    private $giftTitles = [
        'A Best Selling Book',
        'Adventure Day Experience',
        'Bean Boozled Challenge',
        'Bedside Pocket',
        'Camera Nesting Box',
        'Custom Star Map',
        'Day Out Ticket(s)',
        'Engraved Glass',
        'Falconry Experience',
        'Festival Chair',
        'Fitness Equipment',
        'Fitness Tracker',
        'Garden Kneeler',
        'Garden Plaque',
        'Graphics Tablet',
        'Harry Potter Remote Control Wand',
        'Infinity Light',
        'Kitchen Knives Set',
        'Lego Yourself Mini-Figure',
        'Novelty Mug',
        'Personalised Caricature',
        'Personalised Everyone Book',
        'Personalised Map',
        'Personalised Mug',
        'Photo as a Poster',
        'Pouched Tenner',
        'Power Bank',
        'Projection Clock',
        'Reusable Coffee Cup',
        'Slush Puppie Machine',
        'Smartphone Armband',
        'Song Sound Wave Print',
        'Standup Comedy Tickets',
        'Sweet Hamper',
        'Tablet PC',
        'The Official Emoji Game',
        'Treasure Trail',
        'Ukulele',
        'Wall Mural',
        'Wall Sculpture'
    ];

    private $partyTitles = [
        '007 Party',
        '1980s Dance Party',
        'Birthday Yum Birthday Fun',
        'Breakfast at Tiffany\'s',
        'Crazy on the Coast',
        'Dance Till ya Drop',
        'Easter',
        'FIESTA!!!',
        'Fun, Fun, Fun',
        'It Ain\'t Over Til It\'s Over!',
        'Let The Good Times Roll',
        'Let\'s get 2gether',
        'Let\'s Have Fun \'Cause I\'m Twenty-one!',
        'Livin\' La Vida Loca!',
        'Rock On!',
        'School Holiday',
        'Secret Santa Party',
        'Sleepover Hangover',
        'Snooze-a-Palooza',
        'Super-Duper-Party-Hard!',
        'We Gonna Party Like It\'s Your Birthday!',
        'Winter Wonderland',
        'Work Anniversary',
        'You\'re Here for the Party!'
    ];

    /**
     * Returns random gift title (using pre-defined hardcoded array of values)
     * @param array $alreadyUsed
     * @return string
     */
    private function getGiftTitle(array $alreadyUsed)
    {
        do {
            $title = $this->giftTitles[array_rand($this->giftTitles)];
        } while (in_array($title, $alreadyUsed));

        return $title;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->partyTitles as $partyTitle) {
            $giftList = new GiftList();
            $giftList->setFirstName('John');
            $giftList->setEmail('email@localhost');
            $giftList->setTitle($partyTitle);
            $giftList->setDescription('Public gift list');
            $giftList->setIsPublic(true);

            $usedTitles = [];

            for ($j = 0; $j < random_int(5, 7); $j++) {
                $gift = new Gift();
                $gift->setTitle($this->getGiftTitle($usedTitles));
                $usedTitles[] = $gift->getTitle();
                $giftList->addGift($gift);
            }

            $manager->persist($giftList);
        }

        $manager->flush();
    }


}