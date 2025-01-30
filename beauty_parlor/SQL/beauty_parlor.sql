CREATE DATABASE beauty_parlor;
USE beauty_parlor;

-- Table Definitions
CREATE TABLE admin (
    admin_id VARCHAR(10) PRIMARY KEY,
    admin_name VARCHAR(100),
    admin_email VARCHAR(100),
    admin_phone VARCHAR(15)
);

CREATE TABLE customer (
    cust_id VARCHAR(10) PRIMARY KEY,
    cust_name VARCHAR(100),
    cust_phone VARCHAR(15),
    cust_mail VARCHAR(100),
    cust_loc VARCHAR(150)
);

CREATE TABLE staff (
    staff_id VARCHAR(10) PRIMARY KEY,
    staff_name VARCHAR(100),
    staff_phone VARCHAR(15),
    staff_mail VARCHAR(100),
    staff_loc VARCHAR(150),
    staff_role VARCHAR(50)
);

CREATE TABLE services (
    sid VARCHAR(10) PRIMARY KEY,
    sname VARCHAR(100),
    sprice DECIMAL(10,2),
    sduration TIME,
    category VARCHAR(50)
);

CREATE TABLE appointment (
    app_id VARCHAR(10) PRIMARY KEY,
    app_date DATE,
    cust_id VARCHAR(10),
    FOREIGN KEY (cust_id) REFERENCES customer(cust_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE appointment_details (
    pay_id VARCHAR(10) PRIMARY KEY,
    app_id VARCHAR(10),
    cust_id VARCHAR(10),
    pay_date DATE,
    pay_method VARCHAR(50),
    total_bill DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    app_date DATE,
    FOREIGN KEY (app_id) REFERENCES appointment(app_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cust_id) REFERENCES customer(cust_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE appointment_services (
    app_id VARCHAR(10),
    sid VARCHAR(10),
    staff_id VARCHAR(10),
    PRIMARY KEY (app_id, sid),
    FOREIGN KEY (app_id) REFERENCES appointment(app_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (sid) REFERENCES services(sid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE appointment_staff (
    app_id VARCHAR(10),
    staff_id VARCHAR(10),
    PRIMARY KEY (app_id, staff_id),
    FOREIGN KEY (app_id) REFERENCES appointment(app_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE review (
    rev_id VARCHAR(10) PRIMARY KEY,
    cust_id VARCHAR(10),
    rating FLOAT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    FOREIGN KEY (cust_id) REFERENCES customer(cust_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE inventory (
    product_id VARCHAR(10) PRIMARY KEY,
    product_name VARCHAR(100),
    product_category VARCHAR(50),
    quantity INT,
    price DECIMAL(10,2),
    supplier_name VARCHAR(100),
    supplier_contact VARCHAR(50)
);

CREATE TABLE inventory_services (
    product_id VARCHAR(10),
    sid VARCHAR(10),
    quantity_used INT,
    appointment_date DATE,
    app_id VARCHAR(10),
    PRIMARY KEY (product_id, sid, appointment_date, app_id),
    FOREIGN KEY (product_id) REFERENCES inventory(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (sid) REFERENCES services(sid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (app_id) REFERENCES appointment(app_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insert Data
INSERT INTO admin VALUES
('A001', 'Latifa Nishat Nishi', 'latifa@admin.com', '01234567890'),
('A002', 'Admin Two', 'admin2@admin.com', '01634567890');

INSERT INTO customer VALUES
('C001', 'Farzana Akter', '01711234567', 'farzana@gmail.com', 'Gulshan-1, Dhaka'),
('C002', 'Sharmin Rahman', '01819876543', 'sharmin@yahoo.com', 'Banani, Dhaka'),
('C003', 'Nusrat Jahan', '01677889900', 'nusrat@hotmail.com', 'Dhanmondi, Dhaka'),
('C004', 'Taslima Begum', '01912345678', 'taslima@gmail.com', 'Uttara, Dhaka'),
('C005', 'Sabrina Islam', '01551234567', 'sabrina@yahoo.com', 'Mirpur, Dhaka'),
('C006', 'Raisa Khan', '01612345678', 'raisa@gmail.com', 'Mohammadpur, Dhaka'),
('C007', 'Mehreen Hossain', '01911222333', 'mehreen@gmail.com', 'Bashundhara, Dhaka'),
('C008', 'Anika Rahman', '01711555666', 'anika@yahoo.com', 'Baridhara, Dhaka');

INSERT INTO staff VALUES
('S001', 'Sultana Begum', '01733445566', 'sultana@bparlor.com', 'Mirpur, Dhaka', 'Senior Stylist'),
('S002', 'Nasreen Akter', '01844556677', 'nasreen@bparlor.com', 'Mohammadpur, Dhaka', 'Makeup Artist'),
('S003', 'Shahnaz Parvin', '01955667788', 'shahnaz@bparlor.com', 'Dhanmondi, Dhaka', 'Hair Specialist'),
('S004', 'Reshmi Khatun', '01866778899', 'reshmi@bparlor.com', 'Gulshan, Dhaka', 'Nail Artist'),
('S005', 'Tahsina Islam', '01977889900', 'tahsina@bparlor.com', 'Uttara, Dhaka', 'Facial Specialist'),
('S006', 'Roksana Akter', '01688990011', 'roksana@bparlor.com', 'Banani, Dhaka', 'Massage Therapist'),
('S007', 'Hasina Begum', '01899001122', 'hasina@bparlor.com', 'Bashundhara, Dhaka', 'Waxing Specialist'),
('S008', 'Salma Khatun', '01744556677', 'salma@bparlor.com', 'Baridhara, Dhaka', 'Beauty Therapist'),
('S009', 'Farzana Rahman', '01799887766', 'farzana@bparlor.com', 'Mirpur, Dhaka', 'Receptionist'),
('S010', 'Mariam Akter', '01811223344', 'mariam@bparlor.com', 'Uttara, Dhaka', 'Receptionist');

INSERT INTO services VALUES
('SV001', 'Bridal Makeup', 15000.00, '02:00:00', 'Bridal'),
('SV002', 'Hair Treatment', 3000.00, '01:30:00', 'Hair Care'),
('SV003', 'Facial', 2500.00, '01:00:00', 'Makeover'),
('SV004', 'Manicure & Pedicure', 1500.00, '01:30:00', 'Body Care'),
('SV005', 'Full Body Massage', 2000.00, '01:00:00', 'Packages'),
('SV006', 'Hair Cut & Style', 1000.00, '00:45:00', 'Hair Care'),
('SV007', 'Full Body Waxing', 3500.00, '01:30:00', 'Body Care'),
('SV008', 'Threading', 200.00, '00:15:00', 'Body Care');

INSERT INTO appointment VALUES
('AP001', '2025-01-26', 'C001'),
('AP002', '2025-01-26', 'C002'),
('AP003', '2025-01-27', 'C003'),
('AP004', '2025-01-27', 'C004'),
('AP005', '2025-01-28', 'C005'),
('AP006', '2025-01-28', 'C006'),
('AP007', '2025-01-29', 'C007'),
('AP008', '2025-01-29', 'C008');

INSERT INTO appointment_details VALUES
('P001', 'AP001', 'C001', '2025-01-10', 'bKash', 18500.00, 'Confirmed', '2025-01-26'),
('P002', 'AP002', 'C002', '2025-01-25', 'Cash', 7000.00, 'Confirmed', '2025-01-26'),
('P003', 'AP003', 'C003', '2025-01-15', 'Credit Card', 4500.00, 'Pending', '2025-01-27'),
('P004', 'AP004', 'C004', '2025-01-16', 'Nagad', 5500.00, 'Confirmed', '2025-01-27');
('P005', 'AP005', 'C005', '2025-01-26', 'bKash', 6000.00, 'Confirmed', '2025-01-28'),
('P006', 'AP006', 'C006', '2025-01-17', 'Cash', 4700.00, 'Pending', '2025-01-28'),
('P007', 'AP007', 'C007', '2025-01-17', 'Credit Card', 3500.00, 'Confirmed', '2025-01-29'),
('P008', 'AP008', 'C008', '2025-01-28', 'Rocket', 5200.00, 'Confirmed', '2025-01-29');

INSERT INTO appointment_services VALUES
('AP001', 'SV001', 'S002'),
('AP001', 'SV002', 'S003'),
('AP001', 'SV004', 'S004'),
('AP002', 'SV003', 'S005'),
('AP002', 'SV005', 'S006'),
('AP003', 'SV006', 'S001'),
('AP003', 'SV008', 'S001'),
('AP004', 'SV007', 'S007'),
('AP005', 'SV003', 'S005'),
('AP005', 'SV005', 'S006'),
('AP006', 'SV004', 'S004'),
('AP007', 'SV002', 'S003'),
('AP008', 'SV001', 'S002'),
('AP008', 'SV004', 'S004');

INSERT INTO appointment_staff VALUES
('AP001', 'S002'),
('AP001', 'S003'),
('AP001', 'S004'),
('AP002', 'S005'),
('AP002', 'S006'),
('AP003', 'S001'),
('AP004', 'S007'),
('AP005', 'S005'),
('AP005', 'S006'),
('AP006', 'S004'),
('AP007', 'S003'),
('AP008', 'S002'),
('AP008', 'S004');

INSERT INTO review VALUES
('R001', 'C001', 5, 'Excellent bridal makeup and overall service!'),
('R002', 'C002', 4.9, 'Great facial and massage experience'),
('R003', 'C003', 5, 'Very professional haircut service'),
('R004', 'C004', 4.5, 'Satisfied with the waxing service'),
('R005', 'C005', 5, 'Amazing facial and massage combo'),
('R006', 'C006', 3, 'Good service but long waiting time'),
('R007', 'C007', 5, 'Best hair treatment in town'),
('R008', 'C008', 4, 'Wonderful bridal package');

INSERT INTO inventory VALUES
('P001', 'Shampoo', 'Hair Care', 100, 500.00, 'HairCare Co.', '01711111111'),
('P002', 'Conditioner', 'Hair Care', 80, 450.00, 'HairCare Co.', '01711111111'),
('P003', 'Facial Cream', 'Skin Care', 50, 1500.00, 'SkinGlow Ltd.', '01822222222'),
('P004', 'Nail Polish', 'Nail Care', 200, 300.00, 'NailArt Supplies', '01933333333'),
('P005', 'Massage Oil', 'Body Care', 30, 1200.00, 'RelaxPro Ltd.', '01644444444'),
('P006', 'Wax Strips', 'Body Care', 100, 700.00, 'SmoothWax Co.', '01855555555');

INSERT INTO inventory_services VALUES
('P003', 'SV001', 3, '2025-01-26', 'AP001'),
('P001', 'SV002', 2, '2025-01-26', 'AP001'),
('P002', 'SV002', 2, '2025-01-26', 'AP001'),
('P004', 'SV004', 5, '2025-01-26', 'AP001'),
('P003', 'SV003', 1, '2025-01-26', 'AP002'),
('P005', 'SV005', 2, '2025-01-26', 'AP002'),
('P001', 'SV006', 1, '2025-01-27', 'AP003'),
('P006', 'SV008', 1, '2025-01-27', 'AP003'),
('P006', 'SV007', 4, '2025-01-27', 'AP004'),
('P003', 'SV003', 1, '2025-01-28', 'AP005'),
('P005', 'SV005', 2, '2025-01-28', 'AP005'),
('P004', 'SV004', 5, '2025-01-28', 'AP006'),
('P001', 'SV002', 2, '2025-01-29', 'AP007'),
('P002', 'SV002', 2, '2025-01-29', 'AP007'), 
('P003', 'SV001', 3, '2025-01-29', 'AP008'), 
('P004', 'SV004', 5, '2025-01-29', 'AP008');  


