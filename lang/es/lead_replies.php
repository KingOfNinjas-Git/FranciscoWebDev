<?php

// Puntos de partida para responder a un contacto desde el admin. :name es el nombre de la persona, :type su tipo de proyecto.
// Todo lo que esté entre [corchetes] hay que completarlo antes de que el admin deje enviar.
return [
    'templates' => [
        'questions' => [
            'label' => 'Ask a few questions first',
            'subject' => 'Tu proyecto web',
            'body' => "Hola :name,\n\nGracias por tu mensaje sobre tu proyecto (:type). Para darte un plan y un precio claros, ¿puedes contarme un poco más?\n\n- ¿Para quién es la web y qué deben poder hacer los visitantes?\n- ¿Ya tienes logotipo, textos o fotografías, o necesitas ayuda con eso?\n- ¿Hay webs que te gusten y sirvan de referencia?\n- ¿Para cuándo te gustaría tenerla online?\n\nSi te resulta más fácil hablar, dime una buena hora y te llamo.\n\nUn saludo,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
        'quote' => [
            'label' => 'Send a plan and price',
            'subject' => 'Plan y precio para tu web',
            'body' => "Hola :name,\n\nGracias por los detalles. Esto es lo que te propongo para tu proyecto (:type):\n\nLo que construiré: [describir]\nTiempo necesario: [plazo]\nPrecio: [precio]\n\nSi te parece bien, responde a este correo y empezamos. Si quieres cambiar algo, dímelo y ajusto el plan.\n\nUn saludo,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
        'decline' => [
            'label' => 'Politely say no',
            'subject' => 'Sobre tu proyecto web',
            'body' => "Hola :name,\n\nGracias por pensar en mí para tu proyecto (:type). Lamentablemente no puedo aceptarlo en este momento y prefiero decírtelo ya a dejarte esperando.\n\nTe deseo lo mejor con él.\n\nUn saludo,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
    ],
];
