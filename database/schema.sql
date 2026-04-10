-- ViralNest Community Platform - Schema SQL Completo
-- Versão 1.0.0

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ----------------------------
-- Tabela: admin_users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super','admin') DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: system_settings
-- ----------------------------
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `setting_type` ENUM('text','number','boolean','color','textarea','select') DEFAULT 'text',
  `category` VARCHAR(50) DEFAULT 'general',
  `description` VARCHAR(255),
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: cycles
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cycles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `max_users` INT DEFAULT 1000,
  `current_users` INT DEFAULT 0,
  `status` ENUM('active','closed','upcoming') DEFAULT 'upcoming',
  `start_date` DATETIME,
  `end_date` DATETIME,
  `require_invite` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `invite_code` VARCHAR(20) UNIQUE NOT NULL,
  `used_invite_code` VARCHAR(20) DEFAULT NULL,
  `invited_by` INT DEFAULT NULL,
  `points` INT DEFAULT 0,
  `level` VARCHAR(30) DEFAULT 'explorer',
  `role` ENUM('user','moderator') DEFAULT 'user',
  `status` ENUM('active','suspended','pending') DEFAULT 'active',
  `cycle_id` INT DEFAULT NULL,
  `email_verified` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  FOREIGN KEY (`invited_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cycle_id`) REFERENCES `cycles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: points
-- ----------------------------
CREATE TABLE IF NOT EXISTS `points` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `action_type` VARCHAR(50) NOT NULL,
  `points` INT NOT NULL,
  `description` VARCHAR(255),
  `reference_id` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: invites
-- ----------------------------
CREATE TABLE IF NOT EXISTS `invites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `owner_id` INT NOT NULL,
  `used_by` INT DEFAULT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`used_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: groups
-- ----------------------------
CREATE TABLE IF NOT EXISTS `groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) UNIQUE NOT NULL,
  `description` TEXT,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `leader_id` INT NOT NULL,
  `max_members` INT DEFAULT 50,
  `is_private` TINYINT(1) DEFAULT 0,
  `status` ENUM('active','closed') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`leader_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: group_members
-- ----------------------------
CREATE TABLE IF NOT EXISTS `group_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` ENUM('member','moderator') DEFAULT 'member',
  `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_group_user` (`group_id`,`user_id`),
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: plans
-- ----------------------------
CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `billing_cycle` ENUM('monthly','quarterly','annual','lifetime') DEFAULT 'monthly',
  `features` TEXT COMMENT 'JSON array de features',
  `course_discount` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Desconto % em cursos',
  `points_multiplier` DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Multiplicador de pontos',
  `max_groups` INT DEFAULT 1,
  `badge_color` VARCHAR(20) DEFAULT '#FFD700',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: user_subscriptions
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `gateway` ENUM('mercadopago','asaas','efibank','inter') NOT NULL,
  `gateway_subscription_id` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','cancelled','expired','pending') DEFAULT 'pending',
  `price_paid` DECIMAL(10,2),
  `started_at` DATETIME,
  `expires_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: courses
-- ----------------------------
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) UNIQUE NOT NULL,
  `description` TEXT,
  `thumbnail` VARCHAR(255),
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `points_price` INT DEFAULT 0 COMMENT 'Custo em pontos para desbloquear',
  `instructor` VARCHAR(100),
  `level_required` VARCHAR(30) DEFAULT 'explorer',
  `is_free` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: modules
-- ----------------------------
CREATE TABLE IF NOT EXISTS `modules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: lessons
-- ----------------------------
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `video_url` VARCHAR(500),
  `video_type` ENUM('youtube','drive','iframe','vimeo') DEFAULT 'youtube',
  `duration_minutes` INT DEFAULT 0,
  `is_preview` TINYINT(1) DEFAULT 0 COMMENT 'Visível sem compra',
  `points_reward` INT DEFAULT 0 COMMENT 'Pontos ao completar',
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: user_courses (acessos)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user_courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `access_type` ENUM('purchased','points','plan','gift') DEFAULT 'purchased',
  `gateway` VARCHAR(30) DEFAULT NULL,
  `price_paid` DECIMAL(10,2) DEFAULT 0.00,
  `points_spent` INT DEFAULT 0,
  `granted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_course` (`user_id`,`course_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: lesson_progress
-- ----------------------------
CREATE TABLE IF NOT EXISTS `lesson_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `lesson_id` INT NOT NULL,
  `completed` TINYINT(1) DEFAULT 0,
  `completed_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `uniq_user_lesson` (`user_id`,`lesson_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: gateway_settings
-- ----------------------------
CREATE TABLE IF NOT EXISTS `gateway_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gateway` ENUM('mercadopago','asaas','efibank','inter') UNIQUE NOT NULL,
  `is_active` TINYINT(1) DEFAULT 0,
  `credentials` TEXT COMMENT 'JSON com credenciais criptografadas',
  `sandbox_mode` TINYINT(1) DEFAULT 1,
  `webhook_secret` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: transactions
-- ----------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('course','subscription','upgrade') NOT NULL,
  `reference_id` INT DEFAULT NULL,
  `gateway` VARCHAR(30),
  `gateway_tx_id` VARCHAR(255),
  `amount` DECIMAL(10,2),
  `status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  `payload` TEXT COMMENT 'JSON da resposta do gateway',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: notifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200),
  `message` TEXT,
  `type` ENUM('system','invite','level','group','course','payment') DEFAULT 'system',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: whatsapp_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `phone` VARCHAR(30),
  `message` TEXT,
  `event_type` VARCHAR(50),
  `status` ENUM('sent','failed','pending') DEFAULT 'pending',
  `response` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabela: plan_course_access (quais planos liberam quais cursos)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `plan_course_access` (
  `plan_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  PRIMARY KEY (`plan_id`,`course_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- DADOS INICIAIS
-- =========================================

-- Admin padrão (senha: admin123)
INSERT INTO `admin_users` (`name`,`email`,`password`,`role`) VALUES
('Super Admin','admin@viralnest.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','super');

-- Ciclo inicial
INSERT INTO `cycles` (`name`,`max_users`,`current_users`,`status`,`require_invite`,`start_date`) VALUES
('Ciclo Fundadores',1000,0,'active',0,NOW());

-- Configurações do sistema
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`setting_type`,`category`,`description`) VALUES
-- Geral
('site_name','ViralNest','text','general','Nome do site/plataforma'),
('site_tagline','A comunidade que cresce com você','text','general','Slogan da plataforma'),
('site_url','http://localhost','text','general','URL base do site'),
('site_logo','','text','general','URL do logotipo'),
('primary_color','#F59E0B','color','general','Cor primária do tema'),
('secondary_color','#1E293B','color','general','Cor secundária do tema'),
('accent_color','#FBBF24','color','general','Cor de destaque'),
('dark_bg','#0F172A','color','general','Cor de fundo escuro'),
('footer_text','© 2025 ViralNest. Todos os direitos reservados.','text','general','Texto do rodapé'),
-- Cadastro e convites
('allow_registration','true','boolean','registration','Permitir novos cadastros'),
('require_invite_after_cycle','true','boolean','registration','Exigir convite após ciclo inicial'),
('initial_cycle_vacancies','1000','number','registration','Vagas no ciclo inicial gratuito'),
('points_invite','100','number','points','Pontos ao convidar alguém'),
('points_register','50','number','points','Pontos ao se registrar'),
('points_complete_lesson','30','number','points','Pontos ao completar aula'),
('points_complete_course','200','number','points','Pontos ao completar curso'),
-- Níveis
('level_explorer_min','0','number','levels','Pontos mínimos nível Explorer'),
('level_mentor_min','200','number','levels','Pontos mínimos nível Mentor'),
('level_guardian_min','1000','number','levels','Pontos mínimos nível Guardian'),
('level_master_min','3000','number','levels','Pontos mínimos nível Master'),
('level_legend_min','7000','number','levels','Pontos mínimos nível Legend'),
-- Grupos
('max_groups_per_user','3','number','groups','Máximo de grupos por usuário'),
('min_points_create_group','1000','number','groups','Pontos mínimos para criar grupo'),
-- Ranking
('ranking_limit','50','number','ranking','Quantidade de usuários no ranking'),
-- WhatsApp
('whatsell_enabled','false','boolean','whatsapp','Ativar notificações WhatsApp'),
('whatsell_token','','text','whatsapp','Token Bearer da API Whatsell'),
('whatsell_endpoint','https://api.whatsell.online/api/messages/send','text','whatsapp','Endpoint da API Whatsell'),
('whatsell_notify_register','true','boolean','whatsapp','Notificar no cadastro'),
('whatsell_notify_level_up','true','boolean','whatsapp','Notificar ao subir de nível'),
('whatsell_notify_invite','true','boolean','whatsapp','Notificar ao usar convite'),
('whatsell_notify_payment','true','boolean','whatsapp','Notificar pagamento confirmado'),
('whatsell_msg_register','Bem-vindo(a) à {site_name}! Seu cadastro foi confirmado. 🎉','textarea','whatsapp','Mensagem de boas-vindas'),
('whatsell_msg_level_up','Parabéns {name}! Você subiu para o nível {level}! 🏆','textarea','whatsapp','Mensagem de subida de nível'),
('whatsell_msg_invite','Ótima notícia! {invited} usou seu convite e entrou na comunidade! +{points} pontos para você! 🚀','textarea','whatsapp','Mensagem de convite usado'),
('whatsell_msg_payment','Pagamento confirmado! Acesso ao {product} liberado. Bons estudos! 📚','textarea','whatsapp','Mensagem de pagamento confirmado'),
-- Social
('facebook_url','','text','social','URL Facebook'),
('instagram_url','','text','social','URL Instagram'),
('youtube_url','','text','social','URL YouTube'),
('telegram_url','','text','social','URL Telegram');

-- Gateways (estrutura inicial)
INSERT INTO `gateway_settings` (`gateway`,`is_active`,`credentials`,`sandbox_mode`) VALUES
('mercadopago',0,'{}',1),
('asaas',0,'{}',1),
('efibank',0,'{}',1),
('inter',0,'{}',1);

-- Planos padrão
INSERT INTO `plans` (`name`,`slug`,`description`,`price`,`billing_cycle`,`features`,`course_discount`,`points_multiplier`,`max_groups`,`badge_color`,`is_active`,`sort_order`) VALUES
('Gratuito','free','Acesso básico à comunidade',0.00,'monthly','["Acesso à comunidade","Convites ilimitados","Ranking público","1 grupo"]',0.00,1.00,1,'#94A3B8',1,0),
('Explorador','explorer','Para quem quer crescer rápido',29.90,'monthly','["Tudo do Gratuito","10% desconto em cursos","2x pontos em ações","2 grupos","Badge exclusivo"]',10.00,2.00,2,'#3B82F6',1,1),
('Mentor','mentor','Para líderes de comunidade',79.90,'monthly','["Tudo do Explorador","25% desconto em cursos","3x pontos em ações","5 grupos","Acesso a workshops mensais","Badge Mentor"]',25.00,3.00,5,'#8B5CF6',1,2),
('Master','master','Acesso total e vitalício',197.00,'lifetime','["Tudo do Mentor","50% desconto em cursos","5x pontos em ações","Grupos ilimitados","Todos os cursos inclusos","Badge Master Exclusivo","Suporte prioritário"]',50.00,5.00,99,'#F59E0B',1,3);

SET foreign_key_checks = 1;
