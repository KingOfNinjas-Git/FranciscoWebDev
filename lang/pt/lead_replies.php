<?php

// Pontos de partida para responder a um contacto no admin. :name é o nome da pessoa, :type o tipo de projeto.
// Tudo o que estiver entre [parênteses retos] tem de ser preenchido antes de o admin deixar enviar.
return [
    'templates' => [
        'questions' => [
            'label' => 'Ask a few questions first',
            'subject' => 'O teu projeto de site',
            'body' => "Olá :name,\n\nObrigado pela tua mensagem sobre o teu projeto (:type). Para te dar um plano e um preço claros, podes contar-me um pouco mais?\n\n- Para quem é o site e o que devem os visitantes conseguir fazer?\n- Já tens logótipo, textos ou fotografias, ou precisas de ajuda com isso?\n- Há sites de que gostes e possam servir de referência?\n- Para quando gostarias de o ter online?\n\nSe for mais fácil falarmos, diz-me uma boa hora e ligo-te.\n\nCom os melhores cumprimentos,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
        'quote' => [
            'label' => 'Send a plan and price',
            'subject' => 'Plano e preço para o teu site',
            'body' => "Olá :name,\n\nObrigado pelos detalhes. Eis o que te proponho para o teu projeto (:type):\n\nO que vou construir: [descrever]\nTempo necessário: [prazo]\nPreço: [preço]\n\nSe te parecer bem, responde a este email e começamos. Se quiseres mudar alguma coisa, diz-me e ajusto o plano.\n\nCom os melhores cumprimentos,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
        'decline' => [
            'label' => 'Politely say no',
            'subject' => 'Sobre o teu projeto de site',
            'body' => "Olá :name,\n\nObrigado por teres pensado em mim para o teu projeto (:type). Infelizmente não consigo aceitá-lo neste momento e prefiro dizer-te já a deixar-te à espera.\n\nDesejo-te o melhor com ele.\n\nCom os melhores cumprimentos,\nFrancisco Cavaco\nhttps://franciscowebdev.com",
        ],
    ],
];
