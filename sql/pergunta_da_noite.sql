-- Moonspace :: Pergunta da Noite
-- Rode este arquivo UMA vez no phpMyAdmin (aba "SQL") do banco do site.
-- Ele cria 3 tabelas novas e não altera nenhuma tabela existente.

CREATE TABLE IF NOT EXISTS `night_questions` (
  `question_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Qual pergunta foi usada em cada noite (preenchida automaticamente pelo site)
CREATE TABLE IF NOT EXISTS `night_days` (
  `night_date` date NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`night_date`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Respostas: uma por pessoa por noite
CREATE TABLE IF NOT EXISTS `night_answers` (
  `answer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `night_date` date NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `answer` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`answer_id`),
  UNIQUE KEY `night_user` (`night_date`, `user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Perguntas iniciais (dá para adicionar mais pelo próprio site, aba "Perguntas", como admin)
INSERT INTO `night_questions` (`question`, `created_at`) VALUES
('Qual filme você reveria hoje à noite, sem pensar duas vezes?', UTC_TIMESTAMP()),
('O que te tirou o sono esta semana?', UTC_TIMESTAMP()),
('Qual foi a melhor parte do seu dia?', UTC_TIMESTAMP()),
('Que música você colocaria para tocar agora?', UTC_TIMESTAMP()),
('Qual jogo marcou a sua infância?', UTC_TIMESTAMP()),
('Se pudesse estar em qualquer lugar agora, onde estaria?', UTC_TIMESTAMP()),
('Qual conselho você daria para você de 5 anos atrás?', UTC_TIMESTAMP()),
('Qual pequena coisa te deixou feliz hoje?', UTC_TIMESTAMP()),
('Que livro, série ou filme você indicaria para um desconhecido?', UTC_TIMESTAMP()),
('Qual é o seu ritual antes de dormir?', UTC_TIMESTAMP()),
('Qual foi a última coisa que você aprendeu e achou incrível?', UTC_TIMESTAMP()),
('Se a noite de hoje fosse um filme, qual seria o título?', UTC_TIMESTAMP()),
('Qual comida resolve qualquer dia ruim?', UTC_TIMESTAMP()),
('Qual personagem de ficção você gostaria de conhecer?', UTC_TIMESTAMP()),
('O que você está esperando ansiosamente?', UTC_TIMESTAMP()),
('Qual lugar da sua cidade você mais gosta à noite?', UTC_TIMESTAMP()),
('Qual hobby você gostaria de começar?', UTC_TIMESTAMP()),
('Qual foi o melhor final de filme ou série que você já viu?', UTC_TIMESTAMP()),
('Qual cheiro te traz uma boa lembrança?', UTC_TIMESTAMP()),
('Se pudesse aprender uma habilidade da noite para o dia, qual seria?', UTC_TIMESTAMP()),
('Qual foi a conversa mais boa que você teve recentemente?', UTC_TIMESTAMP()),
('Que jogo você está jogando (ou queria estar jogando) agora?', UTC_TIMESTAMP()),
('O que você faria com um dia inteiro livre amanhã?', UTC_TIMESTAMP()),
('Qual frase ficou na sua cabeça esta semana?', UTC_TIMESTAMP()),
('Qual é a sua opinião impopular sobre filmes?', UTC_TIMESTAMP()),
('Você já viu algo no céu que não esqueceu?', UTC_TIMESTAMP()),
('Qual pessoa merecia um agradecimento seu hoje?', UTC_TIMESTAMP()),
('Qual trilha sonora você colocaria na sua vida agora?', UTC_TIMESTAMP()),
('O que te faz perder a noção do tempo?', UTC_TIMESTAMP()),
('Qual pergunta você gostaria que aparecesse aqui amanhã?', UTC_TIMESTAMP());
