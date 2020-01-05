<?php

return [

    /*
     * The API key of a MailChimp account. You can find yours at
     * https://us10.admin.mailchimp.com/account/api-key-popup/.
     */
    'apiKey' => '271523d88fa432a934c13b33760d0bf8-us18',

    /*
     * The listName to use when no listName has been specified in a method.
     */
    'defaultListName' => 'Home Seekers',

    /*
     * Here you can define properties of the lists.
     */
     'lists' => [
         'mabet4u' => [
             'id' => '0a4905501f',
         ],
         'Home Seekers' => [
             'id' => 'fb984d651f',
         ],
         'Home Owners' => [
             'id' => '274e3b0fbd'
         ]
     ],

    /*
     * If you're having trouble with https connections, set this to false.
     */
    'ssl' => false,

];
