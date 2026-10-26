<?php

// Starting points for replying to a lead from the admin. :name is the person's name, :type their kind of project.
// Anything in [square brackets] must be filled in before the admin lets you send.
return [
    'templates' => [
        'questions' => [
            'label' => 'Ask a few questions first',
            'subject' => 'Your website project',
            'body' => "Hi :name,\n\nThanks for your message about your :type project. To give you a clear plan and price, could you tell me a little more?\n\n- Who is the website for, and what should visitors be able to do?\n- Do you already have a logo, texts or photos, or do you need help with those?\n- Are there websites you like as a reference?\n- When would you like it to be online?\n\nIf it is easier to talk, tell me a good time and I will call you.\n\nBest regards,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
        'quote' => [
            'label' => 'Send a plan and price',
            'subject' => 'Plan and price for your website',
            'body' => "Hi :name,\n\nThanks for the details. Here is what I suggest for your :type project:\n\nWhat I will build: [describe it]\nHow long it takes: [time]\nPrice: [price]\n\nIf this looks right, just reply to this email and we can start. If you would like to change anything, tell me and I will adjust the plan.\n\nBest regards,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
        'decline' => [
            'label' => 'Politely say no',
            'subject' => 'About your website project',
            'body' => "Hi :name,\n\nThank you for thinking of me for your :type project. Unfortunately I am not able to take it on at the moment, and I would rather tell you that now than keep you waiting.\n\nI wish you the best with it.\n\nBest regards,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
    ],
];
