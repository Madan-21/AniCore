-- Create the anime_library database
CREATE DATABASE IF NOT EXISTS `24152367`;
USE `24152367`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for table `anime`
DROP TABLE IF EXISTS `anime`;
CREATE TABLE `anime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `release_year` year(4) DEFAULT NULL,
  `episode_count` int(11) DEFAULT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `studio` varchar(100) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `anime`
INSERT INTO `anime` VALUES
('1','Cowboy Bebop','Crime is timeless. By the year 2071, humanity has expanded across the galaxy, filling the surface of other planets with settlements like those on Earth. These new societies are plagued by murder, drug use, and theft, and intergalactic outlaws are hunted by a growing number of tough bounty hunters.\r\n\r\nSpike Spiegel and Jet Black pursue criminals throughout space to make a humble living. Beneath his goofy and aloof demeanor, Spike is haunted by the weight of his violent past. Meanwhile, Jet manages his own troubled memories while taking care of Spike and the Bebop, their ship. The duo is joined by the beautiful con artist Faye Valentine, odd child Edward Wong Hau Pepelu Tivrusky IV, and Ein, a bioengineered Welsh corgi.\r\n\r\nWhile developing bonds and working to catch a colorful cast of criminals, the Bebop crew\'s lives are disrupted by a menace from Spike\'s past. As a rival\'s maniacal plot continues to unravel, Spike must choose between life with his newfound family or revenge for his old wounds.','1998','26','images/posters/cowboy_bebop_1748688296.jpg','images/banners/cowboy_bebop_1748688297.jpg','2025-05-31 16:29:57','','','0.0'),
('2','Fullmetal Alchemist: Brotherhood','After a horrific alchemy experiment goes wrong in the Elric household, brothers Edward and Alphonse are left in a catastrophic new reality. Ignoring the alchemical principle banning human transmutation, the boys attempted to bring their recently deceased mother back to life. Instead, they suffered brutal personal loss: Alphonse\'s body disintegrated while Edward lost a leg and then sacrificed an arm to keep Alphonse\'s soul in the physical realm by binding it to a hulking suit of armor.\n\nThe brothers are rescued by their neighbor Pinako Rockbell and her granddaughter Winry. Known as a bio-mechanical engineering prodigy, Winry creates prosthetic limbs for Edward by utilizing \"automail,\" a tough, versatile metal used in robots and combat armor. After years of training, the Elric brothers set off on a quest to restore their bodies by locating the Philosopher\'s Stone—a powerful gem that allows an alchemist to defy the traditional laws of Equivalent Exchange.\n\nAs Edward becomes an infamous alchemist and gains the nickname \"Fullmetal,\" the boys\' journey embroils them in a growing conspiracy that threatens the fate of the world.\n\n','2009','64','images/posters/fullmetal_alchemist__brotherhood_1748688300.jpg','images/banners/fullmetal_alchemist__brotherhood_1748688300.jpg','2025-05-31 16:30:00',NULL,NULL,NULL),
('3','Shinseiki Evangelion','Fifteen years after a cataclysmic event known as the Second Impact, the world faces a new threat: monstrous celestial beings called Angels invade Tokyo-3 one by one. Mankind is unable to defend themselves against the Angels despite utilizing their most advanced munitions and military tactics. The only hope for human salvation rests in the hands of NERV, a mysterious organization led by the cold Gendou Ikari. NERV operates giant humanoid robots dubbed \"Evangelions\" to combat the Angels with state-of-the-art advanced weaponry and protective barriers known as Absolute Terror Fields.\n\nYears after being abandoned by his father, Shinji Ikari, Gendou\'s 14-year-old son, returns to Tokyo-3. Shinji undergoes a perpetual internal battle against the deeply buried trauma caused by the loss of his mother and the emotional neglect he suffered at the hands of his father. Terrified to open himself up to another, Shinji\'s life is forever changed upon meeting 29-year-old Misato Katsuragi, a high-ranking NERV officer who shows him a free-spirited maternal kindness he has never experienced.\n\nA devastating Angel attack forces Shinji into action as Gendou reveals his true motive for inviting his son back to Tokyo-3: Shinji is the only child capable of efficiently piloting Evangelion Unit-01, a new robot that synchronizes with his biometrics. Despite the brutal psychological trauma brought about by piloting an Evangelion, Shinji defends Tokyo-3 against the angelic threat, oblivious to his father\'s dark machinations.\n\n','1995','26','images/posters/shinseiki_evangelion_1748688302.jpg',NULL,'2025-05-31 16:30:03',NULL,NULL,NULL),
('4','Howl no Ugoku Shiro','That jumbled piece of architecture, that cacophony of hissing steam and creaking joints, with smoke billowing from it as it moves on its own... That castle is home to the magnificent wizard Howl, infamous for both his magical prowess and for being a womanizer—or so the rumor goes in Sophie Hatter\'s small town. Sophie, as the plain daughter of a hatmaker, does not expect much from her future and is content with working hard in the shop. \n\nHowever, Sophie\'s simple life takes a turn for the exciting when she is ensnared in a disturbing situation, and the mysterious wizard appears to rescue her. Unfortunately, this encounter, brief as it may be, spurs the vain and vengeful Witch of the Waste—in a fit of jealousy caused by a past discord with Howl—to put a curse on the maiden, turning her into an old woman.\n\nIn an endeavor to return to normal, Sophie must accompany Howl and a myriad of eccentric companions—ranging from a powerful fire demon to a hopping scarecrow—in his living castle, on a dangerous adventure as a raging war tears their kingdom apart.\n\n','2004','1','images/posters/howl_no_ugoku_shiro_1748688305.jpg','images/banners/howl_no_ugoku_shiro_1748688305.jpg','2025-05-31 16:30:05',NULL,NULL,NULL),
('5','Gintama','Edo is a city that was home to the vigor and ambition of samurai across the country. However, following feudal Japan\'s surrender to powerful aliens known as the \"Amanto,\" those aspirations now seem unachievable. With the once-influential shogunate rebuilt as a puppet government, a new law is passed that promptly prohibits all swords in public. \n\nEnter Gintoki Sakata, an eccentric silver-haired man who always carries around a wooden sword and maintains his stature as a samurai despite the ban. As the founder of Yorozuya, a small business for odd jobs, Gintoki often embarks on endeavors to help other people—though usually in rather strange and unforeseen ways. \n\nAssisted by Shinpachi Shimura, a boy with glasses supposedly learning the way of the samurai; Kagura, a tomboyish girl with superhuman strength and an endless appetite; and Sadaharu, their giant pet dog who loves biting on people\'s heads, the Yorozuya encounter anything from alien royalty to scuffles with local gangs in the ever-changing world of Edo.\n\n','2006','201','images/posters/gintama_1748688307.jpg',NULL,'2025-05-31 16:30:07',NULL,NULL,NULL),
('6','Death Note','Brutal murders, petty thefts, and senseless violence pollute the human world. In contrast, the realm of death gods is a humdrum, unchanging gambling den. The ingenious 17-year-old Japanese student Light Yagami and sadistic god of death Ryuk share one belief: their worlds are rotten.\n\nFor his own amusement, Ryuk drops his Death Note into the human world. Light stumbles upon it, deeming the first of its rules ridiculous: the human whose name is written in this note shall die. However, the temptation is too great, and Light experiments by writing a felon\'s name, which disturbingly enacts his first murder.\n\nAware of the terrifying godlike power that has fallen into his hands, Light—under the alias Kira—follows his wicked sense of justice with the ultimate goal of cleansing the world of all evil-doers. The meticulous mastermind detective L is already on his trail, but as Light\'s brilliance rivals L\'s, the grand chase for Kira turns into an intense battle of wits that can only end when one of them is dead.\n\n','2006','37','images/posters/death_note_1748688310.jpg','images/banners/death_note_1748688310.jpg','2025-05-31 16:30:11',NULL,NULL,NULL),
('7','Sen to Chihiro no Kamikakushi','Stubborn, spoiled, and naïve, 10-year-old Chihiro Ogino is less than pleased when she and her parents discover an abandoned amusement park on the way to their new house. Cautiously venturing inside, she realizes that there is more to this place than meets the eye, as strange things begin to happen once dusk falls. Ghostly apparitions and food that turns her parents into pigs are just the start—Chihiro has unwittingly crossed over into the spirit world. Now trapped, she must summon the courage to live and work amongst spirits, with the help of the enigmatic Haku and the cast of unique characters she meets along the way.\n\nVivid and intriguing, Sen to Chihiro no Kamikakushi tells the story of Chihiro\'s journey through an unfamiliar world as she strives to save her parents and return home.\n\n','2001','1','images/posters/sen_to_chihiro_no_kamikakushi_1748688313.jpg','images/banners/sen_to_chihiro_no_kamikakushi_1748688313.jpg','2025-05-31 16:30:13',NULL,NULL,NULL),
('8','One Piece','Barely surviving in a barrel after passing through a terrible whirlpool at sea, carefree Monkey D. Luffy ends up aboard a ship under attack by fearsome pirates. Despite being a naive-looking teenager, he is not to be underestimated. Unmatched in battle, Luffy is a pirate himself who resolutely pursues the coveted One Piece treasure and the King of the Pirates title that comes with it.\n\nThe late King of the Pirates, Gol D. Roger, stirred up the world before his death by disclosing the whereabouts of his hoard of riches and daring everyone to obtain it. Ever since then, countless powerful pirates have sailed dangerous seas for the prized One Piece only to never return. Although Luffy lacks a crew and a proper ship, he is endowed with a superhuman ability and an unbreakable spirit that make him not only a formidable adversary but also an inspiration to many.\n\nAs he faces numerous challenges with a big smile on his face, Luffy gathers one-of-a-kind companions to join him in his ambitious endeavor, together embracing perils and wonders on their once-in-a-lifetime adventure.\n\n','1999',NULL,'images/posters/one_piece_1748688316.jpg','images/banners/one_piece_1748688316.jpg','2025-05-31 16:30:16',NULL,NULL,NULL),
('9','Hunter x Hunter (2011)','Hunters devote themselves to accomplishing hazardous tasks, all from traversing the world\'s uncharted territories to locating rare items and monsters. Before becoming a Hunter, one must pass the Hunter Examination—a high-risk selection process in which most applicants end up handicapped or worse, deceased.\n\nAmbitious participants who challenge the notorious exam carry their own reason. What drives 12-year-old Gon Freecss is finding Ging, his father and a Hunter himself. Believing that he will meet his father by becoming a Hunter, Gon takes the first step to walk the same path.\n\nDuring the Hunter Examination, Gon befriends the medical student Leorio Paladiknight, the vindictive Kurapika, and ex-assassin Killua Zoldyck. While their motives vastly differ from each other, they band together for a common goal and begin to venture into a perilous world.\n\n','2011','148','images/posters/hunter_x_hunter__2011__1748688319.jpg',NULL,'2025-05-31 16:30:20',NULL,NULL,NULL),
('10','JoJo no Kimyou na Bouken Part 5: Ougon no Kaze','In the coastal city of Naples, corruption is teeming—the police blatantly conspire with outlaws, drugs run rampant around the youth, and the mafia governs the streets with an iron fist. However, various fateful encounters will soon occur.\n\nEnter Giorno Giovanna, a 15-year-old boy with an eccentric connection to the Joestar family, who makes a living out of part-time jobs and pickpocketing. Furthermore, he is gifted with the unexplained Stand ability to give and create life—growing plants from the ground and turning inanimate objects into live animals, an ability he has dubbed \"Gold Experience.\" Fascinated by the might of local gangsters, Giorno has dreamed of rising up in their ranks and becoming a \"Gang-Star,\" a feat made possible by his encounter with Bruno Bucciarati, a member of the Passione gang with his own sense of justice.\n\nJoJo no Kimyou na Bouken: Ougon no Kaze follows the endeavors of Giorno after joining Bruno\'s team while working under Passione, fending off other gangsters and secretly plotting to overthrow their mysterious boss.\n\n','2018','39','images/posters/jojo_no_kimyou_na_bouken_part_5__ougon_no_kaze_1748688323.jpg',NULL,'2025-05-31 16:30:24',NULL,NULL,NULL),
('11','Kimetsu no Yaiba','Ever since the death of his father, the burden of supporting the family has fallen upon Tanjirou Kamado\'s shoulders. Though living impoverished on a remote mountain, the Kamado family are able to enjoy a relatively peaceful and happy life. One day, Tanjirou decides to go down to the local village to make a little money selling charcoal. On his way back, night falls, forcing Tanjirou to take shelter in the house of a strange man, who warns him of the existence of flesh-eating demons that lurk in the woods at night.\n\nWhen he finally arrives back home the next day, he is met with a horrifying sight—his whole family has been slaughtered. Worse still, the sole survivor is his sister Nezuko, who has been turned into a bloodthirsty demon. Consumed by rage and hatred, Tanjirou swears to avenge his family and stay by his only remaining sibling. Alongside the mysterious group calling themselves the Demon Slayer Corps, Tanjirou will do whatever it takes to slay the demons and protect the remnants of his beloved sister\'s humanity.\n\n','2019','26','images/posters/kimetsu_no_yaiba_1748688326.jpg','images/banners/kimetsu_no_yaiba_1748688327.jpg','2025-05-31 16:30:27',NULL,NULL,NULL),
('12','Bleach: Sennen Kessen-hen','Substitute Soul Reaper Ichigo Kurosaki spends his days fighting against Hollows, dangerous evil spirits that threaten Karakura Town. Ichigo carries out his quest with his closest allies: Orihime Inoue, his childhood friend with a talent for healing; Yasutora Sado, his high school classmate with superhuman strength; and Uryuu Ishida, Ichigo\'s Quincy rival.\n\nIchigo\'s vigilante routine is disrupted by the sudden appearance of Asguiaro Ebern, a dangerous Arrancar who heralds the return of Yhwach, an ancient Quincy king. Yhwach seeks to reignite the historic blood feud between Soul Reaper and Quincy, and he sets his sights on erasing both the human world and the Soul Society for good.\n\nYhwach launches a two-pronged invasion into both the Soul Society and Hueco Mundo, the home of Hollows and Arrancar. In retaliation, Ichigo and his friends must fight alongside old allies and enemies alike to end Yhwach\'s campaign of carnage before the world itself comes to an end.\n\n','2022','13','images/posters/bleach__sennen_kessen-hen_1748688329.jpg','images/banners/bleach__sennen_kessen-hen_1748688329.jpg','2025-05-31 16:30:30',NULL,NULL,NULL),
('13','Sword Art Online','Ever since the release of the innovative NerveGear, gamers from all around the globe have been given the opportunity to experience a completely immersive virtual reality. Sword Art Online (SAO), one of the most recent games on the console, offers a gateway into the wondrous world of Aincrad, a vivid, medieval landscape where users can do anything within the limits of imagination. With the release of this worldwide sensation, gaming has never felt more lifelike.\n\nHowever, the idyllic fantasy rapidly becomes a brutal nightmare when SAO\'s creator traps thousands of players inside the game. The \"log-out\" function has been removed, with the only method of escape involving beating all of Aincrad\'s one hundred increasingly difficult levels. Adding to the struggle, any in-game death becomes permanent, ending the player\'s life in the real world.\n\nWhile Kazuto \"Kirito\" Kirigaya was fortunate enough to be a beta-tester for the game, he quickly finds that despite his advantages, he cannot overcome SAO\'s challenges alone. Teaming up with Asuna Yuuki and other talented players, Kirito makes an effort to face the seemingly insurmountable trials head-on. But with difficult bosses and threatening dark cults impeding his progress, Kirito finds that such tasks are much easier said than done.\n\n','2012','25','images/posters/sword_art_online_1748688332.jpg','images/banners/sword_art_online_1748688333.jpg','2025-05-31 16:30:34',NULL,NULL,NULL),
('14','Boku no Hero Academia 3rd Season','As summer arrives for the students at UA Academy, each of these superheroes-in-training puts in their best efforts to become renowned heroes. They head off to a forest training camp run by UA\'s pro heroes, where the students face one another in battle and go through dangerous tests, improving their abilities and pushing past their limits. However, their school trip is suddenly turned upside down when the League of Villains arrives, invading the camp with a mission to capture one of the students. \n\nBoku no Hero Academia 3rd Season follows Izuku \"Deku\" Midoriya, an ambitious student training to achieve his dream of becoming a hero similar to his role model—All Might. Being one of the students caught up amidst the chaos of the villain attack, Deku must take a stand with his classmates and fight for their survival.\n\n','2018','25','images/posters/boku_no_hero_academia_3rd_season_1748688336.jpg','images/banners/boku_no_hero_academia_3rd_season_1748688337.jpg','2025-05-31 16:30:38',NULL,NULL,NULL);

-- Table structure for table `anime_genres`
DROP TABLE IF EXISTS `anime_genres`;
CREATE TABLE `anime_genres` (
  `anime_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  PRIMARY KEY (`anime_id`,`genre_id`),
  KEY `genre_id` (`genre_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `anime_genres_ibfk_1` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE,
  -- CONSTRAINT `anime_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `anime_genres`
INSERT INTO `anime_genres` VALUES
('1','1'),
('1','9'),
('1','14'),
('2','1'),
('2','2'),
('2','4'),
('2','5'),
('3','1'),
('3','4'),
('3','9'),
('3','14'),
('3','15'),
('3','16'),
('4','2'),
('4','4'),
('4','5'),
('4','8'),
('4','14'),
('5','1'),
('5','3'),
('5','9'),
('6','12'),
('6','16'),
('7','2'),
('7','5'),
('7','14'),
('8','1'),
('8','2'),
('8','5'),
('9','1'),
('9','2'),
('9','5'),
('10','1'),
('10','2'),
('11','1'),
('11','12'),
('11','14'),
('12','1'),
('12','2'),
('12','12'),
('13','1'),
('13','2'),
('13','5'),
('13','8'),
('14','1');

-- Table structure for table `contact_messages`
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `contact_messages`
INSERT INTO `contact_messages` VALUES
('1','madan','madan@example.com','hi there','hellooooooo','2','replied','2025-06-02 18:31:04'),
('2','deepesh','deepesh@gmail.com','hello there','this is a test message','9','replied','2025-06-03 00:53:56');

-- Table structure for table `email_verification_tokens`
DROP TABLE IF EXISTS `email_verification_tokens`;
CREATE TABLE `email_verification_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `email_verification_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `genres`
DROP TABLE IF EXISTS `genres`;
CREATE TABLE `genres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `genres`
INSERT INTO `genres` VALUES
('1','Action'),
('2','Adventure'),
('15','Avant Garde'),
('14','Award Winning'),
('3','Comedy'),
('4','Drama'),
('5','Fantasy'),
('6','Horror'),
('7','Mystery'),
('8','Romance'),
('9','Sci-Fi'),
('10','Slice of Life'),
('11','Sports'),
('12','Supernatural'),
('16','Suspense'),
('13','Thriller');

-- Table structure for table `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `reset_tokens`
DROP TABLE IF EXISTS `reset_tokens`;
CREATE TABLE `reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `reset_tokens`
INSERT INTO `reset_tokens` VALUES
('17','6','276caa4dd074d3e0cc728b6b35723c0258f831081fa4ef85892d9ff969a464b5','2025-06-02 20:24:56','2025-06-02 23:09:56');

-- Table structure for table `user_anime_watchlist`
DROP TABLE IF EXISTS `user_anime_watchlist`;
CREATE TABLE `user_anime_watchlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL,
  `status` enum('Watching','Completed','On-Hold','Dropped','Plan to Watch') NOT NULL DEFAULT 'Plan to Watch',
  `user_rating` int(11) DEFAULT NULL,
  `episodes_watched` int(11) DEFAULT NULL,
  `date_added_to_list` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_status_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_anime_unique` (`user_id`,`anime_id`),
  KEY `anime_id` (`anime_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `user_anime_watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  -- CONSTRAINT `user_anime_watchlist_ibfk_2` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `user_anime_watchlist`
INSERT INTO `user_anime_watchlist` VALUES
('1','2','12','Plan to Watch',NULL,NULL,'2025-05-31 16:45:24','2025-05-31 16:45:24'),
('3','2','14','Plan to Watch',NULL,NULL,'2025-05-31 16:51:17','2025-05-31 16:51:17'),
('9','2','1','Plan to Watch',NULL,NULL,'2025-05-31 20:14:26','2025-05-31 20:14:26'),
('11','3','12','Completed','3','3','2025-06-01 20:44:29','2025-06-01 20:44:42'),
('12','3','14','Completed',NULL,NULL,'2025-06-01 21:00:57','2025-06-01 21:01:05'),
('13','3','5','Completed',NULL,NULL,'2025-06-01 21:26:57','2025-06-01 21:26:57'),
('14','2','5','Completed',NULL,NULL,'2025-06-01 22:10:41','2025-06-01 22:10:41'),
('15','2','4','Completed','10',NULL,'2025-06-01 22:56:08','2025-06-01 22:56:08'),
('18','2','6','Plan to Watch',NULL,NULL,'2025-06-02 07:30:52','2025-06-02 07:30:52'),
('19','2','9','Plan to Watch',NULL,NULL,'2025-06-02 07:31:01','2025-06-02 07:31:01'),
('20','2','2','Plan to Watch',NULL,NULL,'2025-06-02 07:34:19','2025-06-02 07:34:19'),
('28','2','10','Plan to Watch',NULL,NULL,'2025-06-02 23:19:06','2025-06-02 23:19:06'),
('30','8','14','Completed',NULL,NULL,'2025-06-03 00:33:00','2025-06-03 00:33:05'),
('31','9','12','Completed','5','13','2025-06-03 00:52:34','2025-06-03 00:52:58');

-- Table structure for table `user_sessions`
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
  -- Foreign keys commented out for easier import
  -- CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user',
  `email_verified` tinyint(1) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`
INSERT INTO `users` VALUES
('1','demo','admin@anicore.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2025-05-31 16:01:35','admin','1','default.jpg'),
('2','madan','madan@example.com','$2y$10$WQ2y32HAp.a.zti5FMLq4OKp0MX1Vw3gQ32bfatfNj1mZyGV1pmui','2025-05-31 16:13:33','user','0','default.jpg'),
('3','sangam','sangam@example.com','$2y$10$ejdXsCQ4z6LpVvEMt810z.lfb0lpowSO9piBRJ4p5KkF8Q1e5HXOa','2025-06-01 20:43:41','user','0','default.jpg'),
('4','hello','hello@example.com','$2y$10$9NyqOHoCx5eFa/mR0FQEoeUdMCCm0OPE.c99Uw1HFymm5eeNCL7Im','2025-06-02 07:05:55','user','0','default.jpg'),
('5','madann','sangamgaming1234@gmail.com','$2y$10$xAPNazDmIuiJFuGSeU4bsuqh9epPtqfcrY28/iLXnC2SC66b8sXkO','2025-06-02 09:52:26','user','0','default.jpg'),
('6','madan1234','pmadan466@gmail.com','$2y$10$cPUBg5wfLQucRr3X/ujubOrKeOFmX4cGg1oRME4d/WzQQwkuTmoJm','2025-06-02 22:19:55','user','0','default.jpg'),
('7','test','test@gmail.com','$2y$12$2CIOjQTn2b0r2T1rWB07FOyEU5L0cBThXuCFvpKSWNHYOJqfvUVHC','2025-06-03 00:31:16','user','0','default.jpg'),
('8','user1','user1@gmail.com','$2y$12$5201p00hZjO3fw/QdBVupuyh1T9K1Dz.6aiCDOvvCzK43/0USmnuK','2025-06-03 00:31:20','user','0','default.jpg'),
('9','deepesh','deepesh@gmail.com','$2y$10$HipDn4nwPr.F55OP.Y5Cju5VYX9M/UcF.E7CqVsh3remr.DzPKH2W','2025-06-03 00:52:17','user','0','default.jpg');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

