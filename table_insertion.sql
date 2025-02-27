-- Insert data into Team
INSERT INTO Team VALUES
(1, 'Red Bull', 100, 6, 7, 250, 'Austria'),
(2, 'Mercedes', 125, 8, 9, 300, 'Germany'),
(3, 'Ferrari', 240, 16, 15, 400, 'Italy'),
(4, 'McLaren', 180, 8, 12, 350, 'UK'),
(5, 'Aston Martin', 5, 0, 0, 20, 'UK'),
(6, 'Alpine', 21, 0, 2, 60, 'France'),
(7, 'Williams', 114, 9, 7, 250, 'UK'),
(8, 'AlphaTauri', 2, 0, 0, 15, 'Italy'),
(9, 'Haas', 0, 0, 0, 5, 'USA'),
(10, 'Alfa Romeo', 10, 0, 0, 30, 'Switzerland');

-- Insert data into Sponsor
INSERT INTO Sponsor VALUES
(1, 'Oracle', 'Technology', 100.50),
(2, 'Petronas', 'Oil & Gas', 200.75),
(3, 'Shell', 'Oil & Gas', 150.30),
(4, 'Vodafone', 'Telecom', 90.20),
(5, 'Aston Martin', 'Automotive', 120.00),
(6, 'Renault', 'Automotive', 80.50),
(7, 'Microsoft', 'Technology', 110.25),
(8, 'Red Bull', 'Beverage', 250.00),
(9, 'Hugo Boss', 'Fashion', 60.75),
(10, 'BMW', 'Automotive', 140.40);

-- Insert data into Driver
INSERT INTO Driver VALUES
(1, 'Max Verstappen', 33, '1997-09-30', 50, 80, 3, 25),
(2, 'Lewis Hamilton', 44, '1985-01-07', 103, 192, 7, 104),
(3, 'Charles Leclerc', 16, '1997-10-16', 5, 20, 0, 15),
(4, 'Lando Norris', 4, '1999-11-13', 0, 10, 0, 2),
(5, 'Fernando Alonso', 14, '1981-07-29', 32, 99, 2, 22),
(6, 'George Russell', 63, '1998-02-15', 1, 10, 0, 3),
(7, 'Carlos Sainz', 55, '1994-09-01', 2, 15, 0, 5),
(8, 'Sergio Perez', 11, '1990-01-26', 6, 30, 0, 5),
(9, 'Pierre Gasly', 10, '1996-02-07', 1, 5, 0, 1),
(10, 'Kevin Magnussen', 20, '1992-10-05', 0, 3, 0, 0);

-- Insert data into Team_Sponsor
INSERT INTO Team_Sponsor VALUES
(1, 1), (2, 2), (3, 3), (4, 4), (5, 5), (6, 6), (7, 7), (8, 8), (9, 9), (8, 1);

-- Insert data into Driver_Sponsor
INSERT INTO Driver_Sponsor VALUES
(1, 8), (2, 2), (3, 3), (4, 4), (5, 5), (6, 6), (7, 7), (8, 8), (9, 9), (10, 10);

-- Insert data into Principal_Managed
INSERT INTO Principal_Managed VALUES
(1, 1, 'Christian Horner', '1973-11-16', 'UK', '2005-01-01'),
(2, 2, 'Toto Wolff', '1972-01-12', 'Austria', '2013-01-01'),
(3, 3, 'Frederic Vasseur', '1968-05-28', 'France', '2023-01-01'),
(4, 4, 'Andrea Stella', '1971-02-22', 'Italy', '2023-01-01'),
(5, 5, 'Mike Krack', '1972-03-18', 'Germany', '2022-01-01'),
(6, 6, 'Otmar Szafnauer', '1964-08-13', 'USA', '2021-01-01'),
(7, 7, 'James Vowles', '1979-06-20', 'UK', '2023-01-01'),
(8, 8, 'Franz Tost', '1956-01-20', 'Austria', '2006-01-01'),
(9, 9, 'Guenther Steiner', '1965-04-07', 'Italy', '2016-01-01'),
(10, 10, 'Alessandro Alunni Bravi', '1974-04-23', 'Italy', '2023-01-01');

-- Insert data into Car
INSERT INTO Car VALUES
(1, 'RB20', 'Honda', 2024, 5, 3, 10),
(2, 'W15', 'Mercedes', 2024, 1, 2, 6),
(3, 'SF24', 'Ferrari', 2024, 2, 3, 8),
(4, 'MCL61', 'Mercedes', 2024, 0, 1, 5),
(5, 'AMR24', 'Mercedes', 2024, 0, 0, 4),
(6, 'A524', 'Renault', 2024, 0, 0, 2),
(7, 'FW46', 'Mercedes', 2024, 0, 0, 1),
(8, 'AT05', 'Honda', 2024, 0, 0, 0),
(9, 'VF-24', 'Ferrari', 2024, 0, 0, 0),
(10, 'C44', 'Ferrari', 2024, 0, 0, 1);

-- Insert data into Manufactures
INSERT INTO Manufactures VALUES
(1, 1), (2, 2), (3, 3), (4, 4), (5, 5), (6, 6), (7, 7), (8, 8), (9, 9), (10, 10);

-- Insert data into Circuit
INSERT INTO Circuit VALUES
(1, 'Monza', 53, 'Italy', 'Italian GP'),
(2, 'Silverstone', 52, 'UK', 'British GP'),
(3, 'Spa-Francorchamps', 44, 'Belgium', 'Belgian GP'),
(4, 'Suzuka', 53, 'Japan', 'Japanese GP'),
(5, 'Monaco', 78, 'Monaco', 'Monaco GP'),
(6, 'Interlagos', 71, 'Brazil', 'Brazilian GP'),
(7, 'Austin', 56, 'USA', 'US GP'),
(8, 'Jeddah', 50, 'Saudi Arabia', 'Saudi Arabian GP'),
(9, 'Baku', 51, 'Azerbaijan', 'Azerbaijan GP'),
(10, 'Yas Marina', 58, 'UAE', 'Abu Dhabi GP');
