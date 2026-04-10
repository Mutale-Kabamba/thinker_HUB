-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2026 at 12:39 AM
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
-- Database: `store_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `CustomerName` varchar(100) NOT NULL,
  `Region` varchar(50) DEFAULT NULL,
  `SignupDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`CustomerID`, `CustomerName`, `Region`, `SignupDate`) VALUES
(1, 'Alice Walker', 'East', '2023-01-15'),
(2, 'Bob Miller', 'Central', '2023-02-10'),
(3, 'Charlie Davis', 'West', '2023-03-05'),
(4, 'Diana Prince', 'South', '2023-03-22'),
(5, 'Evan Wright', 'East', '2023-04-18'),
(6, 'Fiona Gallagher', 'Central', '2023-05-30'),
(7, 'George Martin', 'West', '2023-06-12'),
(8, 'Hannah Abbott', 'South', '2023-07-25'),
(9, 'Ian Malcolm', 'East', '2023-08-05'),
(10, 'Julia Child', 'West', '2023-09-14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `OrderDate` date DEFAULT NULL,
  `OrderAmount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `CustomerID`, `OrderDate`, `OrderAmount`) VALUES
(101, 1, '2024-01-05', 450.00),
(102, 1, '2024-02-15', 600.00),
(103, 2, '2024-01-10', 300.50),
(104, 2, '2024-03-22', 250.00),
(105, 3, '2024-02-05', 1200.00),
(106, 4, '2024-01-18', 815.00),
(107, 4, '2024-04-02', 200.00),
(108, 5, '2024-02-28', 150.00),
(109, 5, '2024-03-15', 75.00),
(110, 6, '2024-01-20', 550.00),
(111, 6, '2024-04-10', 400.00),
(112, 7, '2024-02-14', 210.00),
(113, 8, '2024-03-01', 730.00),
(114, 8, '2024-04-05', 450.00),
(115, 9, '2024-01-30', 480.00),
(116, 9, '2024-03-12', 320.00),
(117, 10, '2024-02-10', 900.00),
(118, 10, '2024-04-20', 150.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
