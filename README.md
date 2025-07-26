# 🛡️ SafeSpace PH

**Live Website:** http://safespaceph.byethost31.com/  
**GitHub Repository:** https://github.com/b1llchavez/SafeSpacePH

SafeSpace PH is a web application designed to provide accessible legal support and promote awareness of the Safe Spaces Act (Republic Act No. 11313) in the Philippines. The platform connects victims of gender-based sexual harassment with volunteer lawyers offering free (pro bono) legal services and consultations.

This project was developed by first-year students as the final requirement for the course **Web Design with Client-Side Scripting (IT0043L)** at FEU Institute of Technology.

---

## 🚀 Key Features

* **User Registration & Verification:** Secure account creation for victims and a verification process for volunteer lawyers to ensure credibility.
* **Secure Violation Reporting:** A confidential form that allows users to report violations of the Safe Spaces Act and securely upload evidence.
* **Appointment Booking:** Victims can schedule appointments with available volunteer lawyers based on their case details.
* **Client Dashboard:** Users can track appointment history, manage bookings, and view lawyer profiles.
* **Lawyer Dashboard:** Volunteer lawyers can manage availability, view appointment requests, and respond to client concerns.
* **Admin Dashboard:** Platform administrators can verify users, manage violation reports, and monitor overall activity.
* **Educational Resources:** Accessible information about the Safe Spaces Act, FAQs, and user rights to enhance public understanding.

---

## 🛠️ Technology Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **APIs & Services:**
    * Gmail SMTP: For email notifications
    * Leaflet API: For map integration
* **Hosting:** Byethost

---

## 💻 Getting Started

To set up a local development environment:

1.  **Install a Local Server** Download and install XAMPP, WAMP, or MAMP that supports PHP & MySQL.

2.  **Start Server Services** Start Apache and MySQL from your control panel.

3.  **Clone the Repository** Paste this in your server’s root directory (e.g., `C:/xampp/htdocs/`):
    ```bash
    git clone [https://github.com/b1llchavez/SafeSpacePH.git](https://github.com/b1llchavez/SafeSpacePH.git)
    ```

4.  **Set Up the Database**
    * Open `phpMyAdmin`.
    * Create a new database (e.g., `safespaceph`).
    * Import the `.sql` file included in the repository into the newly created database.
    * Update the `connection.php` file (or equivalent) with your local database credentials.

5.  **Run the Application** Navigate to `http://localhost/SafeSpacePH` in your browser.

---

## 🧪 Usage & Testing Accounts

Use the following test credentials to explore each user role:

* 🔹 **Client**
    * **Email:** `client@safespaceph.com`
    * **Password:** `123`
    * *Access features like reporting violations and booking appointments.*

* 🔹 **Lawyer**
    * **Email:** `lawyer@safespaceph.com`
    * **Password:** `123`
    * *Manage appointments, availability, and view client requests.*

* 🔹 **Admin**
    * **Email:** `admin@safespaceph.com`
    * **Password:** `123`
    * *Verify users, manage reports, and oversee platform activity.*

---

## 🖼️ Screenshots

Here’s a glimpse of the SafeSpace PH platform in action:

<img width="2874" height="1391" alt="CleanShot 2025-07-27 at 01 51 32@2x" src="https://github.com/user-attachments/assets/de9c5089-ca55-4960-a935-1bf4bbf9def5" />

#### 🏠 Homepage
*The main landing page invites users to connect with volunteer lawyers.*

<img width="1919" height="934" alt="image" src="https://github.com/user-attachments/assets/03ae8de2-72b7-49c0-aca2-15d55fc95263" />

#### 👤 Client Dashboard
*Report violations, manage your appointments, and browse lawyer profiles.*

<img width="1919" height="932" alt="image" src="https://github.com/user-attachments/assets/3ba12c3a-cad3-4ba0-8448-3c4d946f160a" />

#### 👨‍⚖️ Lawyer Dashboard
*Volunteer lawyers can manage schedules and respond to client needs.*

<img width="1919" height="932" alt="image" src="https://github.com/user-attachments/assets/8c403f3c-0593-4e3c-8b9d-16e33ecdbeb0" />

#### 🛡️ Admin Dashboard
*View overall system activities, user verifications, and violation reports.*

---

## 👥 The Team

This full-stack project was collaboratively developed by:

* Gerard Doroja
* Bill Mamorno
* Alexandra Gwen Morales

---

## 🙏 Acknowledgments

We express our deepest gratitude to **Dr. Alexander A. Hernandez**, our course instructor, for his guidance and support throughout this project.

---

## 🌍 Aligned with SDG 16

This project supports United Nations Sustainable Development Goal 16:
> Promote peaceful and inclusive societies for sustainable development, provide access to justice for all, and build effective, accountable institutions at all levels.
