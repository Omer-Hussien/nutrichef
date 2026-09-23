-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2026 at 03:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_recipe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-utensils',
  `color_hex` varchar(7) DEFAULT '#4CAF50'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `icon`, `color_hex`) VALUES
(1, 'Breakfast', 'fa-sun', '#FF9800'),
(2, 'Lunch', 'fa-cloud-sun', '#4CAF50'),
(3, 'Dinner', 'fa-moon', '#3F51B5'),
(4, 'Snacks', 'fa-cookie', '#FF5722'),
(5, 'Desserts', 'fa-ice-cream', '#E91E63'),
(6, 'Smoothies', 'fa-blender', '#00BCD4'),
(7, 'Salads', 'fa-leaf', '#8BC34A'),
(8, 'Soups', 'fa-mug-hot', '#795548');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `ingredient_name` varchar(100) NOT NULL,
  `calories_per_100g` decimal(7,2) DEFAULT 0.00,
  `protein_per_100g` decimal(7,2) DEFAULT 0.00,
  `carbs_per_100g` decimal(7,2) DEFAULT 0.00,
  `fat_per_100g` decimal(7,2) DEFAULT 0.00,
  `fiber_per_100g` decimal(7,2) DEFAULT 0.00,
  `sugar_per_100g` decimal(7,2) DEFAULT 0.00,
  `sodium_per_100g` decimal(7,2) DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'grams'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `calories_per_100g`, `protein_per_100g`, `carbs_per_100g`, `fat_per_100g`, `fiber_per_100g`, `sugar_per_100g`, `sodium_per_100g`, `unit`) VALUES
(1, 'Chicken Breast', 165.00, 31.00, 0.00, 3.60, 0.00, 0.00, 74.00, 'grams'),
(2, 'Brown Rice', 216.00, 5.00, 44.80, 1.80, 3.50, 0.70, 10.00, 'grams'),
(3, 'Broccoli', 34.00, 2.80, 6.60, 0.40, 2.60, 1.70, 33.00, 'grams'),
(4, 'Egg', 155.00, 12.60, 1.10, 10.60, 0.00, 1.10, 124.00, 'grams'),
(5, 'Oats', 389.00, 16.90, 66.30, 6.90, 10.60, 1.00, 2.00, 'grams'),
(6, 'Banana', 89.00, 1.10, 22.80, 0.30, 2.60, 12.20, 1.00, 'grams'),
(7, 'Olive Oil', 884.00, 0.00, 0.00, 100.00, 0.00, 0.00, 2.00, 'grams'),
(8, 'Salmon', 208.00, 20.40, 0.00, 13.40, 0.00, 0.00, 59.00, 'grams'),
(9, 'Spinach', 23.00, 2.90, 3.60, 0.40, 2.20, 0.40, 79.00, 'grams'),
(10, 'Greek Yogurt', 59.00, 10.00, 3.60, 0.40, 0.00, 3.20, 36.00, 'grams'),
(11, 'Tomato', 18.00, 0.90, 3.90, 0.20, 1.20, 2.60, 5.00, 'grams'),
(12, 'Garlic', 149.00, 6.40, 33.10, 0.50, 2.10, 1.00, 17.00, 'grams'),
(13, 'Lemon', 29.00, 1.10, 9.30, 0.30, 2.80, 2.50, 2.00, 'grams'),
(14, 'Avocado', 160.00, 2.00, 8.50, 14.70, 6.70, 0.70, 7.00, 'grams'),
(15, 'Almond Milk', 17.00, 0.60, 0.60, 1.10, 0.20, 0.10, 73.00, 'grams'),
(16, 'Sweet Potato', 86.00, 1.60, 20.10, 0.10, 3.00, 4.20, 55.00, 'grams'),
(17, 'Black Beans', 132.00, 8.90, 23.70, 0.50, 8.70, 0.30, 1.00, 'grams'),
(18, 'Quinoa', 222.00, 8.10, 39.40, 3.60, 5.00, 1.60, 13.00, 'grams'),
(19, 'Honey', 304.00, 0.30, 82.40, 0.00, 0.20, 82.10, 4.00, 'grams'),
(20, 'Whole Wheat Bread', 247.00, 13.00, 41.30, 3.40, 6.00, 5.60, 457.00, 'grams'),
(21, 'Cottage Cheese', 98.00, 11.10, 3.40, 4.30, 0.00, 2.70, 364.00, 'grams'),
(22, 'Blueberries', 57.00, 0.70, 14.50, 0.30, 2.40, 10.00, 1.00, 'grams'),
(23, 'Chia Seeds', 486.00, 16.50, 42.10, 30.70, 34.40, 0.00, 16.00, 'grams'),
(24, 'Kale', 49.00, 4.30, 8.80, 0.90, 3.60, 2.30, 38.00, 'grams'),
(25, 'Lentils', 116.00, 9.00, 20.10, 0.40, 7.90, 1.80, 2.00, 'grams'),
(26, 'Coconut Milk', 197.00, 2.00, 2.80, 21.30, 2.20, 2.80, 15.00, 'grams'),
(27, 'Turmeric', 312.00, 9.70, 67.10, 3.30, 22.70, 3.20, 38.00, 'grams'),
(28, 'Ginger Root', 80.00, 1.80, 17.80, 0.80, 2.00, 1.70, 13.00, 'grams'),
(29, 'Almonds', 579.00, 21.20, 21.60, 49.90, 12.50, 4.40, 1.00, 'grams'),
(30, 'Bell Pepper', 31.00, 1.00, 6.00, 0.30, 2.10, 4.20, 4.00, 'grams'),
(31, 'Zucchini', 17.00, 1.20, 3.10, 0.30, 1.00, 2.50, 8.00, 'grams'),
(32, 'Feta Cheese', 264.00, 14.20, 4.10, 21.30, 0.00, 4.10, 1116.00, 'grams'),
(33, 'Chickpeas', 164.00, 8.90, 27.40, 2.60, 7.60, 4.80, 24.00, 'grams'),
(34, 'Cauliflower', 25.00, 1.90, 5.00, 0.30, 2.00, 1.90, 30.00, 'grams'),
(35, 'Canned Tuna', 116.00, 25.50, 0.00, 0.80, 0.00, 0.00, 396.00, 'grams'),
(36, 'Whole Milk', 61.00, 3.20, 4.80, 3.30, 0.00, 5.20, 44.00, 'grams'),
(37, 'Peanut Butter', 588.00, 25.10, 20.10, 50.40, 6.00, 9.00, 429.00, 'grams'),
(38, 'Mango', 60.00, 0.80, 15.00, 0.40, 1.60, 13.70, 1.00, 'grams'),
(39, 'Flaxseeds', 534.00, 18.30, 28.90, 42.20, 27.30, 1.60, 30.00, 'grams'),
(40, 'Cucumber', 15.00, 0.70, 3.60, 0.10, 0.50, 1.70, 2.00, 'grams');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_date` date NOT NULL,
  `meal_type` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `servings_planned` decimal(4,2) DEFAULT 1.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plans`
--

INSERT INTO `meal_plans` (`plan_id`, `user_id`, `plan_date`, `meal_type`, `recipe_id`, `servings_planned`, `notes`, `created_at`) VALUES
(11, 1, '2026-07-01', 'Breakfast', 16, 1.00, NULL, '2026-07-01 14:58:05');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `prep_time_min` int(11) DEFAULT 0,
  `cook_time_min` int(11) DEFAULT 0,
  `servings` int(11) DEFAULT 1,
  `difficulty` enum('Easy','Medium','Hard') DEFAULT 'Medium',
  `dietary_type` enum('None','Vegetarian','Vegan','Gluten-Free','Keto','Paleo') DEFAULT 'None',
  `image_url` varchar(255) DEFAULT 'default_recipe.jpg',
  `instructions` text NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `total_calories` decimal(8,2) DEFAULT 0.00,
  `total_protein` decimal(8,2) DEFAULT 0.00,
  `total_carbs` decimal(8,2) DEFAULT 0.00,
  `total_fat` decimal(8,2) DEFAULT 0.00,
  `total_fiber` decimal(8,2) DEFAULT 0.00,
  `view_count` int(11) DEFAULT 0,
  `rating_avg` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `title`, `description`, `category_id`, `user_id`, `prep_time_min`, `cook_time_min`, `servings`, `difficulty`, `dietary_type`, `image_url`, `instructions`, `is_featured`, `total_calories`, `total_protein`, `total_carbs`, `total_fat`, `total_fiber`, `view_count`, `rating_avg`, `created_at`) VALUES
(1, 'Grilled Chicken with Brown Rice', 'A wholesome, protein-packed meal perfect for muscle building and weight management.', 2, 1, 10, 25, 2, 'Easy', 'None', 'Grilled Chicken with Brown Rice.jpg', '1. Season chicken breast with garlic, salt, and pepper.\n2. Grill on medium-high heat for 6-7 minutes per side.\n3. Cook brown rice according to package instructions.\n4. Steam broccoli for 4-5 minutes until tender-crisp.\n5. Serve chicken over rice with broccoli on the side.\n6. Drizzle with olive oil and squeeze of lemon.', 1, 420.00, 52.00, 36.00, 8.50, 5.20, 9, 5.00, '2026-06-27 03:32:37'),
(2, 'Spinach & Avocado Smoothie', 'A nutrient-dense green smoothie loaded with vitamins, healthy fats, and natural energy.', 6, 1, 5, 0, 1, 'Easy', 'Vegan', 'Spinach & Avocado Smoothie.jpg', '1. Add almond milk to blender first.\r\n2. Add fresh spinach leaves.\r\n3. Add half an avocado, peeled and pitted.\r\n4. Add one banana for sweetness.\r\n5. Add a drizzle of honey (optional).\r\n6. Blend on high for 60 seconds until smooth.\r\n7. Pour into a glass and serve immediately.', 1, 327.20, 6.07, 42.90, 17.44, 11.04, 14, 5.00, '2026-06-27 03:32:37'),
(3, 'Salmon with Quinoa & Roasted Sweet Potato', 'An omega-3 rich dinner packed with complete proteins and complex carbohydrates.', 3, 1, 15, 30, 2, 'Medium', 'None', 'Salmon with Quinoa & Roasted Sweet Potato.jpg', '1. Preheat oven to 200°C.\n2. Cube sweet potato, toss with olive oil, roast 25 minutes.\n3. Cook quinoa with 2:1 water ratio for 15 minutes.\n4. Season salmon with lemon, garlic, salt and pepper.\n5. Pan-sear salmon skin-side down for 4 minutes, flip for 3 minutes.\n6. Serve salmon over quinoa with sweet potato on the side.\n7. Garnish with fresh lemon slices.', 1, 580.00, 42.00, 55.00, 18.00, 7.50, 2, 5.00, '2026-06-27 03:32:37'),
(4, 'Greek Yogurt Parfait with Oats and Banana', 'A quick and nutritious breakfast that provides sustained energy throughout the morning.', 1, 1, 5, 0, 1, 'Easy', 'Vegetarian', 'Greek Yogurt Parfait with Oats and Banana.jpg', '1. Spoon Greek yogurt into a glass or bowl.\n2. Layer with rolled oats.\n3. Slice banana and add on top.\n4. Drizzle with honey.\n5. Repeat layers if desired.\n6. Serve immediately or refrigerate overnight for overnight oats effect.', 1, 320.00, 16.80, 55.20, 3.20, 5.80, 3, 4.00, '2026-06-27 03:32:37'),
(5, 'Black Bean & Tomato Taco Bowl', 'A vibrant, plant-based bowl bursting with flavour, fibre, and essential nutrients.', 2, 1, 10, 15, 2, 'Easy', 'Vegan', 'Black Bean & Tomato Taco Bowl.jpg', '1. Cook black beans in a pot with garlic and cumin for 10 minutes.\n2. Dice fresh tomatoes and set aside.\n3. Prepare brown rice or quinoa as the base.\n4. Season beans with salt, pepper, and lime juice.\n5. Assemble bowl: rice/quinoa, black beans, diced tomato.\n6. Top with sliced avocado and fresh cilantro.\n7. Drizzle with olive oil and serve.', 0, 380.00, 16.50, 62.00, 8.80, 14.20, 5, 5.00, '2026-06-27 03:32:37'),
(6, 'Scrambled Eggs with Whole Wheat Toast', 'A classic protein-rich breakfast that fuels your morning with quality nutrition.', 1, 1, 3, 7, 1, 'Easy', 'Vegetarian', 'Scrambled Eggs with Whole Wheat Toast.jpg', '1. Whisk eggs with a pinch of salt and pepper.\n2. Heat pan over medium-low heat with a drizzle of olive oil.\n3. Pour in eggs and gently push from edges to center.\n4. Remove from heat when slightly underdone (residual heat finishes cooking).\n5. Toast whole wheat bread until golden.\n6. Serve eggs on toast garnished with fresh herbs.', 0, 410.00, 26.00, 42.50, 14.80, 4.20, 2, 0.00, '2026-06-27 03:32:37'),
(7, 'Turmeric Lentil Soup', 'A warming anti-inflammatory soup rich in plant-based protein and golden spices.', 8, 1, 10, 30, 4, 'Easy', 'Vegan', 'Turmeric Lentil Soup.jpg', '1. Sauté diced onion and garlic in olive oil for 3 minutes.\n2. Add turmeric, cumin, and ginger. Stir for 1 minute.\n3. Add rinsed lentils and 800ml vegetable broth.\n4. Simmer 25 minutes until lentils are soft.\n5. Add diced tomatoes and season to taste.\n6. Blend half the soup for a creamy texture.\n7. Serve garnished with fresh lemon juice.', 1, 380.00, 22.00, 58.00, 5.50, 18.00, 1, 5.00, '2026-06-27 03:32:37'),
(8, 'Blueberry Chia Overnight Oats', 'A no-cook breakfast loaded with antioxidants, omega-3s and slow-release energy.', 1, 1, 5, 0, 1, 'Easy', 'Vegetarian', 'Blueberry Chia Overnight Oats.jpg', '1. Combine oats and chia seeds in a jar.\n2. Pour in almond milk and stir well.\n3. Add a drizzle of honey.\n4. Seal jar and refrigerate overnight (min 6 hours).\n5. In the morning, top with fresh blueberries.\n6. Add a sprinkle of almonds for crunch.\n7. Enjoy cold straight from the jar.', 1, 345.00, 11.20, 52.00, 9.80, 11.50, 2, 5.00, '2026-06-27 03:32:37'),
(9, 'Kale & Chickpea Buddha Bowl', 'A vibrant plant-based bowl bursting with nutrients, colour and satisfying textures.', 2, 1, 15, 20, 2, 'Medium', 'Vegan', 'Kale & Chickpea Buddha Bowl.jpg', '1. Roast chickpeas with olive oil and paprika at 200°C for 20 minutes until crispy.\n2. Massage kale with a pinch of salt and lemon juice.\n3. Cook quinoa as base.\n4. Slice bell pepper and cucumber.\n5. Assemble: quinoa base, kale, roasted chickpeas, vegetables.\n6. Drizzle with tahini dressing (tahini + lemon + garlic + water).\n7. Top with sesame seeds.', 1, 510.00, 22.00, 68.00, 14.50, 16.00, 1, 4.00, '2026-06-27 03:32:37'),
(10, 'Coconut Mango Smoothie Bowl', 'A tropical immunity-boosting smoothie bowl topped with almonds and fresh fruit.', 6, 1, 8, 0, 1, 'Easy', 'Vegan', 'Coconut Mango Smoothie Bowl.jpg', '1. Blend frozen mango chunks with coconut milk until smooth.\n2. Add a teaspoon of flaxseeds to the blender.\n3. Pour into a wide bowl — mixture should be thick.\n4. Top with sliced fresh mango.\n5. Add a handful of blueberries.\n6. Scatter crushed almonds on top.\n7. Drizzle with honey and serve immediately.', 1, 390.00, 6.50, 55.00, 17.00, 7.80, 2, 0.00, '2026-06-27 03:32:37'),
(11, 'Tuna & Avocado Salad', 'A protein-packed, healthy-fat salad ready in under 10 minutes — ideal for a light lunch.', 7, 1, 8, 0, 2, 'Easy', 'Gluten-Free', 'Tuna & Avocado Salad.jpg', '1. Drain canned tuna and flake into a bowl.\n2. Dice avocado and add to the bowl.\n3. Chop cherry tomatoes in half.\n4. Add thinly sliced red onion.\n5. Squeeze lemon juice over everything.\n6. Add olive oil, salt, and black pepper.\n7. Toss gently and serve on a bed of mixed greens.', 0, 320.00, 28.00, 12.00, 18.50, 7.20, 0, 0.00, '2026-06-27 03:32:37'),
(12, 'Cauliflower Fried Rice', 'A low-carb take on fried rice — all the flavour with a fraction of the calories.', 2, 1, 10, 12, 2, 'Easy', 'Vegetarian', 'Cauliflower Fried Rice.jpg', '1. Grate or pulse cauliflower into rice-sized pieces.\n2. Heat olive oil in a large wok over high heat.\n3. Scramble 2 eggs in the wok and set aside.\n4. Stir-fry cauliflower rice for 3-4 minutes.\n5. Add diced bell pepper and frozen peas.\n6. Return eggs and add soy sauce, sesame oil, ginger.\n7. Toss everything together and serve immediately.', 0, 280.00, 14.50, 22.00, 12.00, 6.50, 3, 0.00, '2026-06-27 03:32:37'),
(13, 'Peanut Butter Banana Smoothie', 'A creamy, high-protein post-workout smoothie that tastes like a dessert.', 6, 1, 5, 0, 1, 'Easy', 'Vegetarian', 'Peanut Butter Banana Smoothie.jpg', '1. Add almond milk to blender.\n2. Add one frozen banana (sliced).\n3. Add 2 tablespoons of peanut butter.\n4. Add a drizzle of honey.\n5. Optional: add a scoop of vanilla protein powder.\n6. Blend until smooth and creamy.\n7. Pour into a glass and enjoy immediately.', 0, 420.00, 14.50, 52.00, 18.50, 4.80, 2, 0.00, '2026-06-27 03:32:37'),
(14, 'Greek Salmon Fillet Salad', 'Mediterranean-inspired salad with omega-3 rich salmon, feta and crisp vegetables.', 7, 1, 10, 12, 2, 'Medium', 'Gluten-Free', 'Greek Salmon Fillet Salad.jpg', '1. Season salmon with olive oil, lemon zest, oregano, salt.\n2. Pan-sear salmon skin-side down for 4 minutes, flip for 3 minutes.\n3. Chop tomatoes, cucumber, and bell pepper.\n4. Combine vegetables in a bowl.\n5. Add crumbled feta cheese and olives.\n6. Make dressing: olive oil + lemon juice + dried oregano.\n7. Plate salad, top with flaked salmon, drizzle dressing.', 1, 445.00, 38.00, 14.00, 25.00, 4.50, 0, 5.00, '2026-06-27 03:32:37'),
(15, 'Keto Avocado & Cottage Cheese Power Bowl', 'A rich, low-carb savoury snack packed with healthy fats and slow-digesting casein protein.', 4, 1, 5, 0, 1, 'Easy', 'Keto', 'Keto Avocado & Cottage Cheese Power Bowl.jpg', '1. Dice half an avocado into chunks.\r\n2. Spoon cottage cheese into a small serving bowl.\r\n3. Top with the diced avocado.\r\n4. Drizzle with olive oil.\r\n5. Season generously with salt and cracked black pepper.\r\n6. Enjoy immediately as a high-fat fuel source.', 1, 334.00, 14.92, 10.88, 26.92, 5.36, 73, 5.00, '2026-06-27 07:34:55'),
(16, 'Paleo Garlic Lemon Salmon with Spinach', 'A clean, nutrient-dense dinner rich in omega-3 fatty acids and antioxidants. 100% dairy and grain free.', 3, 1, 10, 12, 2, 'Medium', 'Paleo', 'Paleo Garlic Lemon Salmon with Spinach.jpg', '1. Season salmon fillets with minced garlic, salt, and black pepper.\r\n2. Heat olive oil in a skillet over medium-high heat.\r\n3. Place salmon skin-side down and sear for 5 minutes.\r\n4. Flip salmon and cook for another 4 minutes.\r\n5. Add fresh spinach to the skillet during the last 2 minutes to wilt.\r\n6. Squeeze fresh lemon juice over the fish and greens before serving.', 1, 699.20, 54.87, 9.70, 49.04, 3.25, 48, 5.00, '2026-06-27 07:34:55'),
(17, 'Berry Chia Seed Pudding', 'A naturally sweetened, antioxidant-rich dessert loaded with dietary fibre and omega-3s.', 5, 1, 10, 0, 2, 'Easy', 'Vegetarian', 'Berry Chia Seed Pudding.jpg', '1. Whisk chia seeds and almond milk together in a bowl.\n2. Stir in raw honey until completely dissolved.\n3. Let sit for 10 minutes, then whisk again to prevent clumping.\n4. Cover and refrigerate for at least 2 hours (or overnight).\n5. Before serving, top with fresh blueberries and crushed almonds.', 1, 240.00, 7.50, 26.00, 11.00, 9.80, 15, 4.00, '2026-06-27 07:34:55'),
(18, 'Keto Peanut Butter Coconut Fat Bombs', 'Satiating, bite-sized sweet treats designed to crush sugar cravings while keeping you firmly in ketosis.', 5, 1, 15, 0, 4, 'Easy', 'Keto', 'Keto Peanut Butter Coconut Fat Bombs.jpg', '1. Gently gently warm peanut butter and coconut milk in a saucepan over low heat.\n2. Stir constantly until melted and fully combined.\n3. Remove from heat and stir in ground flaxseeds and crushed almonds.\n4. Spoon mixture into silicone mini-muffin cups.\n5. Freeze for 30 minutes until solid.\n6. Pop out of molds and store in the refrigerator.', 0, 195.00, 6.20, 4.50, 17.50, 3.10, 6, 5.00, '2026-06-27 07:34:55');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit` varchar(30) DEFAULT 'grams'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`id`, `recipe_id`, `ingredient_id`, `quantity`, `unit`) VALUES
(1, 1, 1, 200.00, 'grams'),
(2, 1, 2, 100.00, 'grams'),
(3, 1, 3, 100.00, 'grams'),
(4, 1, 7, 10.00, 'ml'),
(5, 1, 12, 5.00, 'grams'),
(11, 3, 8, 200.00, 'grams'),
(12, 3, 18, 100.00, 'grams'),
(13, 3, 16, 150.00, 'grams'),
(14, 3, 7, 15.00, 'ml'),
(15, 3, 13, 30.00, 'grams'),
(16, 4, 10, 150.00, 'grams'),
(17, 4, 5, 50.00, 'grams'),
(18, 4, 6, 100.00, 'grams'),
(19, 4, 19, 15.00, 'grams'),
(20, 5, 17, 150.00, 'grams'),
(21, 5, 11, 100.00, 'grams'),
(22, 5, 2, 100.00, 'grams'),
(23, 5, 14, 80.00, 'grams'),
(24, 6, 4, 150.00, 'grams'),
(25, 6, 20, 80.00, 'grams'),
(26, 6, 7, 10.00, 'ml'),
(27, 7, 25, 200.00, 'grams'),
(28, 7, 27, 5.00, 'grams'),
(29, 7, 28, 10.00, 'grams'),
(30, 7, 11, 100.00, 'grams'),
(31, 7, 7, 15.00, 'ml'),
(32, 8, 5, 60.00, 'grams'),
(33, 8, 23, 20.00, 'grams'),
(34, 8, 15, 200.00, 'ml'),
(35, 8, 22, 80.00, 'grams'),
(36, 8, 19, 10.00, 'grams'),
(37, 9, 33, 150.00, 'grams'),
(38, 9, 24, 80.00, 'grams'),
(39, 9, 18, 100.00, 'grams'),
(40, 9, 30, 60.00, 'grams'),
(41, 9, 7, 15.00, 'ml'),
(42, 10, 38, 150.00, 'grams'),
(43, 10, 26, 150.00, 'ml'),
(44, 10, 39, 10.00, 'grams'),
(45, 10, 22, 50.00, 'grams'),
(46, 10, 29, 20.00, 'grams'),
(47, 11, 35, 150.00, 'grams'),
(48, 11, 14, 100.00, 'grams'),
(49, 11, 11, 80.00, 'grams'),
(50, 11, 13, 20.00, 'grams'),
(51, 11, 7, 10.00, 'ml'),
(52, 12, 34, 300.00, 'grams'),
(53, 12, 4, 100.00, 'grams'),
(54, 12, 30, 100.00, 'grams'),
(55, 12, 7, 15.00, 'ml'),
(56, 12, 28, 5.00, 'grams'),
(57, 13, 6, 120.00, 'grams'),
(58, 13, 37, 30.00, 'grams'),
(59, 13, 15, 250.00, 'ml'),
(60, 13, 19, 10.00, 'grams'),
(61, 14, 8, 160.00, 'grams'),
(62, 14, 32, 50.00, 'grams'),
(63, 14, 11, 100.00, 'grams'),
(64, 14, 40, 80.00, 'grams'),
(65, 14, 7, 15.00, 'ml'),
(74, 17, 23, 40.00, 'grams'),
(75, 17, 15, 250.00, 'ml'),
(76, 17, 19, 20.00, 'grams'),
(77, 17, 22, 60.00, 'grams'),
(78, 17, 29, 15.00, 'grams'),
(79, 18, 37, 80.00, 'grams'),
(80, 18, 26, 60.00, 'ml'),
(81, 18, 39, 20.00, 'grams'),
(82, 18, 29, 25.00, 'grams'),
(110, 16, 8, 250.00, 'grams'),
(111, 16, 9, 100.00, 'grams'),
(112, 16, 12, 10.00, 'grams'),
(113, 16, 13, 30.00, 'grams'),
(114, 16, 7, 15.00, 'ml'),
(139, 2, 15, 200.00, 'ml'),
(140, 2, 9, 60.00, 'grams'),
(141, 2, 14, 100.00, 'grams'),
(142, 2, 6, 100.00, 'grams'),
(143, 2, 19, 10.00, 'grams'),
(148, 15, 21, 120.00, 'grams'),
(149, 15, 14, 80.00, 'grams'),
(150, 15, 7, 10.00, 'ml');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `recipe_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 1, 1, 5, 'Perfect post-workout meal. High protein and easy to prep!', '2026-06-27 03:32:37'),
(2, 2, 1, 5, 'So refreshing and creamy. My go-to morning smoothie.', '2026-06-27 03:32:37'),
(3, 3, 1, 5, 'Restaurant quality at home. The sweet potato really makes it.', '2026-06-27 03:32:37'),
(4, 4, 1, 4, 'Great overnight version too — make it the night before for a super quick breakfast.', '2026-06-27 03:32:37'),
(5, 7, 1, 5, 'Beautiful golden colour and incredibly warming. Perfect for cold days.', '2026-06-27 03:32:37'),
(6, 8, 1, 5, 'Prep this on Sunday and have breakfast sorted for the week!', '2026-06-27 03:32:37'),
(7, 9, 1, 4, 'Colourful, satisfying and fully plant-based. Love the crispy chickpeas.', '2026-06-27 03:32:37'),
(8, 14, 1, 5, 'Incredible flavour combination. The feta with salmon is a game changer.', '2026-06-27 03:32:37'),
(9, 15, 1, 5, 'Super creamy and keeps me full for hours. Perfect afternoon keto fuel!', '2026-06-27 07:34:55'),
(10, 16, 1, 5, 'The garlic and lemon combination on the salmon is restaurant quality.', '2026-06-27 07:34:55'),
(11, 17, 1, 4, 'Great texture! I make a batch on Sunday for quick weeknight desserts.', '2026-06-27 07:34:55'),
(12, 18, 1, 5, 'These literally saved my keto diet. Curbs sweet cravings instantly.', '2026-06-27 07:34:55'),
(13, 5, 1, 5, 'Amazing food!', '2026-06-29 14:09:24');

-- --------------------------------------------------------

--
-- Table structure for table `saved_recipes`
--

CREATE TABLE `saved_recipes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_recipes`
--

INSERT INTO `saved_recipes` (`id`, `user_id`, `recipe_id`, `saved_at`) VALUES
(15, 1, 16, '2026-06-29 14:57:55'),
(17, 1, 15, '2026-07-01 14:58:31');

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

CREATE TABLE `search_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `search_query` varchar(255) DEFAULT NULL,
  `searched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `activity_level` enum('Sedentary','Lightly Active','Moderately Active','Very Active','Extra Active') DEFAULT 'Moderately Active',
  `dietary_preference` enum('None','Vegetarian','Vegan','Gluten-Free','Keto','Paleo') DEFAULT 'None',
  `health_goal` enum('Lose Weight','Maintain Weight','Gain Weight','Build Muscle') DEFAULT 'Maintain Weight',
  `profile_picture` varchar(255) DEFAULT 'default_avatar.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `age`, `gender`, `weight_kg`, `height_cm`, `activity_level`, `dietary_preference`, `health_goal`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@nutrichef.com', '$2y$10$uS5qmKSTvVs.dTKkA6IQV..TpWggBtFtMl0jrvrh0N2/8S.Mn0b9u', 'System Admin', 30, 'Male', 70.00, 175.00, 'Moderately Active', 'None', 'Maintain Weight', 'default_avatar.png', '2026-06-27 03:32:37', '2026-06-29 13:42:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_allergens`
--

CREATE TABLE `user_allergens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `allergen` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD UNIQUE KEY `ingredient_name` (`ingredient_name`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`recipe_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saved_recipes`
--
ALTER TABLE `saved_recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_allergens`
--
ALTER TABLE `user_allergens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `saved_recipes`
--
ALTER TABLE `saved_recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_allergens`
--
ALTER TABLE `user_allergens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meal_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plans_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_recipes`
--
ALTER TABLE `saved_recipes`
  ADD CONSTRAINT `saved_recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_recipes_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `search_history`
--
ALTER TABLE `search_history`
  ADD CONSTRAINT `search_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_allergens`
--
ALTER TABLE `user_allergens`
  ADD CONSTRAINT `user_allergens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
