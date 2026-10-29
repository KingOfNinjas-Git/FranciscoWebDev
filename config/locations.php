<?php

/*
 * The towns that have their own "web developer in ..." page (/web-developer/<town>, /pt/programador-web/<town>,
 * /es/desarrollador-web/<town>). Each page combines the shared text in lang/*\/site.php (services, process, questions)
 * with what is written here, so no two pages are copies of each other.
 *
 * Keep it honest: say what is true about the place and about working with someone based nearby. Never invent clients,
 * results or distances. To add a town, add an entry here (all three languages) and it appears in the sitemap and the footer.
 */
return [
    'seixal' => [
        'name' => 'Seixal',
        'blurb' => [
            'en' => 'Seixal is where I work from. The municipality runs from the old riverside to Amora, Corroios and Fernão Ferro, with a healthy mix of family shops, cafés, trades and local services that are mostly found by people searching on their phones.',
            'pt' => 'O Seixal é onde trabalho. O concelho vai da zona ribeirinha até à Amora, Corroios e Fernão Ferro, com uma boa mistura de comércio familiar, cafés, profissionais por conta própria e serviços locais que as pessoas procuram sobretudo no telemóvel.',
            'es' => 'Seixal es donde trabajo. El municipio va desde la zona ribereña hasta Amora, Corroios y Fernão Ferro, con una buena mezcla de comercios familiares, cafés, oficios y servicios locales que la gente busca sobre todo desde el móvil.',
        ],
        'near' => [
            'en' => 'I work from here, so meeting in person is easy if you prefer it to a video call.',
            'pt' => 'Trabalho daqui, por isso é fácil encontrarmo-nos pessoalmente, se preferir a uma videochamada.',
            'es' => 'Trabajo desde aquí, así que es fácil vernos en persona si lo prefieres a una videollamada.',
        ],
        'businesses' => [
            'en' => ['Cafés, pastry shops and restaurants', 'Tradespeople and repair services', 'Shops, boutiques and local services', 'Clinics, studios and small associations'],
            'pt' => ['Cafés, pastelarias e restaurantes', 'Profissionais de obras e serviços de reparação', 'Lojas, boutiques e serviços locais', 'Clínicas, estúdios e pequenas associações'],
            'es' => ['Cafés, pastelerías y restaurantes', 'Profesionales de oficios y servicios de reparación', 'Tiendas, boutiques y servicios locales', 'Clínicas, estudios y pequeñas asociaciones'],
        ],
        'places' => ['Amora', 'Arrentela', 'Corroios', 'Fernão Ferro', 'Paio Pires'],
    ],

    'almada' => [
        'name' => 'Almada',
        'blurb' => [
            'en' => 'Almada is where I was born, and I know it well, from the riverfront at Cacilhas to Cova da Piedade, Pragal and the Charneca. Businesses here share an audience with Lisbon across the river, and a clear, fast website helps people find and choose you first.',
            'pt' => 'Almada é onde nasci e conheço-a bem, da zona ribeirinha de Cacilhas a Cova da Piedade, o Pragal e a Charneca. Os negócios daqui partilham o público com Lisboa, do outro lado do rio, e um site claro e rápido ajuda as pessoas a encontrarem-vos e a escolherem-vos primeiro.',
            'es' => 'Almada es donde nací y la conozco bien, desde la ribera de Cacilhas hasta Cova da Piedade, Pragal y la Charneca. Los negocios de aquí comparten público con Lisboa, al otro lado del río, y una web clara y rápida ayuda a que te encuentren y te elijan primero.',
        ],
        'near' => [
            'en' => 'Almada is a short drive from Seixal, so meeting in person is easy to arrange.',
            'pt' => 'Almada fica a pouca distância de carro do Seixal, por isso é fácil marcarmos um encontro pessoal.',
            'es' => 'Almada está a poca distancia en coche de Seixal, así que es fácil quedar en persona.',
        ],
        'businesses' => [
            'en' => ['Restaurants and cafés around Cacilhas and Cova da Piedade', 'Shops and local services', 'Health, wellness and fitness studios', 'Associations, schools and event organisers'],
            'pt' => ['Restaurantes e cafés em Cacilhas e na Cova da Piedade', 'Lojas e serviços locais', 'Estúdios de saúde, bem-estar e fitness', 'Associações, escolas e organizadores de eventos'],
            'es' => ['Restaurantes y cafés en Cacilhas y Cova da Piedade', 'Tiendas y servicios locales', 'Estudios de salud, bienestar y fitness', 'Asociaciones, escuelas y organizadores de eventos'],
        ],
        'places' => ['Cacilhas', 'Cova da Piedade', 'Pragal', 'Laranjeiro', 'Charneca de Caparica'],
    ],

    'costa-da-caparica' => [
        'name' => 'Costa da Caparica',
        'blurb' => [
            'en' => 'Costa da Caparica lives by its beaches. Many visitors search on their phones, often in English, so a site that loads fast on mobile and speaks more than one language is not a luxury here, it is how people choose where to eat, stay and book.',
            'pt' => 'A Costa da Caparica vive das suas praias. Muitos visitantes pesquisam no telemóvel, muitas vezes em inglês, por isso um site que carrega depressa no telemóvel e fala mais do que uma língua não é um luxo: é assim que as pessoas escolhem onde comer, ficar e reservar.',
            'es' => 'Costa da Caparica vive de sus playas. Muchos visitantes buscan desde el móvil, a menudo en inglés, así que una web que carga rápido en el móvil y habla más de un idioma no es un lujo: es como la gente elige dónde comer, alojarse y reservar.',
        ],
        'near' => [
            'en' => 'It is on the same side of the river, a drive away, and I am happy to meet there or online.',
            'pt' => 'Fica do mesmo lado do rio, a uma viagem de carro, e terei todo o gosto em encontrar-me consigo aí ou online.',
            'es' => 'Está en la misma orilla del río, a un trayecto en coche, y me encantará quedar allí o en línea.',
        ],
        'businesses' => [
            'en' => ['Beach restaurants and snack bars', 'Surf schools and activity providers', 'Holiday rentals and guesthouses', 'Shops and services for residents'],
            'pt' => ['Restaurantes e bares de praia', 'Escolas de surf e empresas de atividades', 'Alojamento local e casas de hóspedes', 'Lojas e serviços para residentes'],
            'es' => ['Restaurantes y chiringuitos de playa', 'Escuelas de surf y empresas de actividades', 'Alquileres vacacionales y casas de huéspedes', 'Tiendas y servicios para residentes'],
        ],
        'places' => ['Fonte da Telha', 'Cova do Vapor', 'Caparica'],
    ],

    'barreiro' => [
        'name' => 'Barreiro',
        'blurb' => [
            'en' => 'Barreiro has a strong industrial and railway history and is filling with new cafés, studios and small companies. A website is a simple way to show what is new and to be found by people who still think of the town as only what it used to be.',
            'pt' => 'O Barreiro tem uma forte história industrial e ferroviária e está a encher-se de novos cafés, estúdios e pequenas empresas. Um site é uma forma simples de mostrar o que há de novo e de ser encontrado por quem ainda pensa na cidade só como era antes.',
            'es' => 'Barreiro tiene una fuerte historia industrial y ferroviaria y se está llenando de nuevos cafés, estudios y pequeñas empresas. Una web es una forma sencilla de mostrar lo nuevo y de que te encuentren quienes aún piensan en la ciudad solo como era antes.',
        ],
        'near' => [
            'en' => 'It is across the bay from Seixal, so a visit is easy to arrange when it helps, and everything else works well online.',
            'pt' => 'Fica do outro lado da baía, em frente ao Seixal, por isso uma visita é fácil de combinar quando ajuda, e tudo o resto funciona bem online.',
            'es' => 'Está al otro lado de la bahía, frente a Seixal, así que es fácil organizar una visita cuando ayuda, y todo lo demás funciona bien en línea.',
        ],
        'businesses' => [
            'en' => ['Small companies and industrial suppliers', 'Cafés, restaurants and bakeries', 'Creative studios and local associations', 'Shops and service providers'],
            'pt' => ['Pequenas empresas e fornecedores industriais', 'Cafés, restaurantes e padarias', 'Estúdios criativos e associações locais', 'Lojas e prestadores de serviços'],
            'es' => ['Pequeñas empresas y proveedores industriales', 'Cafés, restaurantes y panaderías', 'Estudios creativos y asociaciones locales', 'Tiendas y proveedores de servicios'],
        ],
        'places' => ['Lavradio', 'Alto do Seixalinho', 'Santo André', 'Verderena'],
    ],

    'setubal' => [
        'name' => 'Setúbal',
        'blurb' => [
            'en' => 'Setúbal combines a working port, the Arrábida natural park and a well-known food scene, so many local businesses serve both residents and visitors, often in more than one language. Being easy to find, and easy to book, is what turns a visit to the page into a customer.',
            'pt' => 'Setúbal junta um porto ativo, o Parque Natural da Arrábida e uma gastronomia muito conhecida, por isso muitos negócios locais servem residentes e visitantes, muitas vezes em mais do que uma língua. Ser fácil de encontrar e fácil de reservar é o que transforma uma visita à página num cliente.',
            'es' => 'Setúbal reúne un puerto activo, el Parque Natural de la Arrábida y una gastronomía muy conocida, así que muchos negocios locales atienden a residentes y visitantes, a menudo en más de un idioma. Ser fácil de encontrar y fácil de reservar es lo que convierte una visita a la página en un cliente.',
        ],
        'near' => [
            'en' => 'It is further down the peninsula, so we would usually talk online, and I am glad to travel when it makes sense.',
            'pt' => 'Fica mais abaixo na península, por isso falaríamos normalmente online, e deslocar-me-ei com gosto quando fizer sentido.',
            'es' => 'Está más abajo en la península, así que normalmente hablaríamos en línea, y viajaré con gusto cuando tenga sentido.',
        ],
        'businesses' => [
            'en' => ['Restaurants, seafood and wine businesses', 'Tourism, boat trips and guesthouses', 'Shops and craft producers', 'Services for companies around the port'],
            'pt' => ['Restaurantes, marisqueiras e negócios de vinho', 'Turismo, passeios de barco e alojamentos', 'Lojas e produtores artesanais', 'Serviços para empresas em torno do porto'],
            'es' => ['Restaurantes, marisquerías y negocios de vino', 'Turismo, paseos en barco y alojamientos', 'Tiendas y productores artesanales', 'Servicios para empresas en torno al puerto'],
        ],
        'places' => ['Azeitão', 'Arrábida'],
    ],

    'sesimbra' => [
        'name' => 'Sesimbra',
        'blurb' => [
            'en' => 'Sesimbra is a fishing town turned seaside destination. Restaurants, diving and boat tours and guesthouses depend on being found online, in English as much as in Portuguese, and on a booking or contact step that is quick on a phone.',
            'pt' => 'Sesimbra é uma vila piscatória que se tornou destino de praia. Restaurantes, mergulho e passeios de barco e alojamentos dependem de serem encontrados online, tanto em inglês como em português, e de um passo de reserva ou contacto rápido no telemóvel.',
            'es' => 'Sesimbra es un pueblo pesquero convertido en destino de playa. Restaurantes, buceo y paseos en barco y alojamientos dependen de que los encuentren en línea, tanto en inglés como en portugués, y de un paso de reserva o contacto rápido en el móvil.',
        ],
        'near' => [
            'en' => 'It is a drive from Seixal; we would meet online, or in person when it helps.',
            'pt' => 'Fica a uma viagem de carro do Seixal; falaríamos online, ou pessoalmente quando ajudar.',
            'es' => 'Está a un trayecto en coche de Seixal; hablaríamos en línea, o en persona cuando ayude.',
        ],
        'businesses' => [
            'en' => ['Restaurants and fish markets', 'Diving, sailing and boat tours', 'Guesthouses and holiday rentals', 'Shops and local services'],
            'pt' => ['Restaurantes e mercados de peixe', 'Mergulho, vela e passeios de barco', 'Casas de hóspedes e alojamento local', 'Lojas e serviços locais'],
            'es' => ['Restaurantes y mercados de pescado', 'Buceo, vela y paseos en barco', 'Casas de huéspedes y alquileres vacacionales', 'Tiendas y servicios locales'],
        ],
        'places' => ['Quinta do Conde', 'Santana'],
    ],
];
