
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Privacy Policy | BRO360</title>
    <meta name="description" content="BRO360 Privacy Policy - Bangsamoro Revenue Office">

    <link rel="icon" href="{{ asset('images/bro360-icon.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --green-950: #102c20;
            --green-900: #153b2a;
            --green-800: #1c5038;
            --green-700: #286747;
            --gold: #d8b65c;
            --text: #34423a;
            --muted: #718078;
            --border: #e5ebe7;
            --background: #f5f7f5;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.75;
        }

        a {
            color: var(--green-700);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .topbar {
            background: var(--green-950);
            border-bottom: 4px solid var(--gold);
            padding: 18px 6%;
        }

        .topbar-inner {
            max-width: 1180px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 13px;
            color: white;
        }

        .brand img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand-subtitle {
            color: #d4dfd7;
            font-size: 10px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .back-link {
            color: white;
            font-size: 13px;
            font-weight: 600;
        }

        .hero {
            background: linear-gradient(120deg, var(--green-950), var(--green-800));
            color: white;
            padding: 65px 20px 75px;
            text-align: center;
        }

        .hero-label {
            color: var(--gold);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .hero h1 {
            font-size: clamp(30px, 5vw, 43px);
            font-weight: 800;
            margin-bottom: 12px;
        }

        .hero p {
            color: #dce8df;
            font-size: 14px;
        }

        .hero-line {
            width: 55px;
            height: 3px;
            background: var(--gold);
            margin: 24px auto 0;
        }

        .page-container {
            max-width: 1100px;
            margin: -30px auto 60px;
            padding: 0 20px;
            position: relative;
        }

        .policy-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(16, 44, 32, .06);
            overflow: hidden;
        }

        .policy-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 22px 38px;
            border-bottom: 1px solid var(--border);
            background: #fcfdfc;
            color: var(--muted);
            font-size: 12px;
        }

        .policy-content {
            padding: 38px;
        }

        .intro {
            padding: 22px 25px;
            background: #f3f7f4;
            border-left: 4px solid var(--gold);
            border-radius: 5px;
            margin-bottom: 35px;
        }

        .intro p {
            margin: 0;
        }

        .policy-section {
            margin-bottom: 32px;
            scroll-margin-top: 25px;
        }

        .policy-section h2 {
            color: var(--green-900);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 29px;
            height: 29px;
            background: #edf3ee;
            color: var(--green-800);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
        }

        .policy-section p {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .policy-section ul {
            padding-left: 24px;
            margin: 12px 0;
        }

        .policy-section li {
            padding-left: 5px;
            margin-bottom: 7px;
            font-size: 14px;
        }

        .policy-section strong {
            color: var(--green-900);
        }

        .contact-card {
            background: #f5f8f5;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 22px;
            margin-top: 15px;
        }

        .contact-card p {
            margin-bottom: 5px;
        }

        .policy-footer {
            background: var(--green-950);
            color: #dce8df;
            text-align: center;
            padding: 30px 20px;
        }

        .policy-footer strong {
            color: white;
        }

        .policy-footer p {
            font-size: 12px;
            margin: 5px 0;
        }

        .footer-gold {
            color: var(--gold);
        }

        @media(max-width: 650px) {
            .topbar {
                padding: 15px 20px;
            }

            .brand-subtitle {
                font-size: 8px;
            }

            .back-link {
                font-size: 11px;
            }

            .hero {
                padding: 48px 20px 65px;
            }

            .policy-content {
                padding: 25px 20px;
            }

            .policy-meta {
                padding: 18px 20px;
            }

            .policy-section h2 {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <div class="topbar-inner">

        <div class="brand">
            <img src="{{ asset('images/mfbm.png') }}"
                 alt="BRO360 Logo"
                 onerror="this.style.display='none'">

            <div>
                <div class="brand-name">BRO360</div>
                <div class="brand-subtitle">Bangsamoro Revenue Office</div>
            </div>
        </div>

        <a href="{{ url('/') }}" class="back-link">
            Back to BRO360
        </a>

    </div>
</header>

<section class="hero">
    <div class="hero-label">Bangsamoro Revenue Office</div>
    <h1>Privacy Policy</h1>
    <p>Your privacy, information, and data protection matter to us.</p>
    <div class="hero-line"></div>
</section>

<main class="page-container">

    <div class="policy-card">

        <div class="policy-meta">
            <span>Document: BRO360 Privacy Policy</span>
            <span>Last Updated: November 17, 2026</span>
        </div>

        <div class="policy-content">

            <div class="intro">
                <p>
                    The Bangsamoro Revenue Office (BRO), under the Ministry
                    of Finance and Budget Management (MFBM), is committed
                    to protecting personal information processed through
                    the BRO360 system. This policy explains how information
                    is collected, used, stored, protected, and disclosed
                    in accordance with applicable data protection laws.
                </p>
            </div>

            <section class="policy-section">
                <h2><span class="section-number">01</span> Introduction</h2>

                <p>
                    BRO360 is an internal digital management system designed
                    to support the administration and monitoring of Programs,
                    Activities, and Projects (PAPs), task assignments,
                    workflow reviews, document routing, accomplishment
                    reporting, and related office operations.
                </p>

                <p>
                    This Privacy Policy is guided by Republic Act No. 10173,
                    otherwise known as the Data Privacy Act of 2012, its
                    Implementing Rules and Regulations, applicable issuances
                    of the National Privacy Commission, and relevant
                    government policies.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">02</span> Information We Collect</h2>

                <p>BRO360 may collect and process the following information:</p>

                <ul>
                    <li><strong>Account information:</strong> Full name, email address, account identifiers, assigned office, division, and user role.</li>
                    <li><strong>Work information:</strong> PAP details, task assignments, deadlines, progress, accomplishment reports, reviews, and approval records.</li>
                    <li><strong>Documents and attachments:</strong> Reports, photographs, files, and other documents uploaded, generated, or managed through the system.</li>
                    <li><strong>Technical information:</strong> Login records, access timestamps, session information, and system activity logs.</li>
                    <li><strong>Google account information:</strong> Basic account details received through Google authentication when a user chooses to sign in using Google.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">03</span> Purpose of Information Processing</h2>

                <p>Information collected through BRO360 is used for legitimate government and official operational purposes, including:</p>

                <ul>
                    <li>Managing and monitoring office programs, activities, and projects.</li>
                    <li>Facilitating task assignments, reviews, approvals, and workflow processes.</li>
                    <li>Monitoring implementation progress and accomplishments.</li>
                    <li>Managing official documents and related records.</li>
                    <li>Generating reports and management summaries.</li>
                    <li>Managing user accounts and access permissions.</li>
                    <li>Maintaining system security, accountability, and audit trails.</li>
                    <li>Complying with applicable laws and government requirements.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">04</span> Google Account and Google Drive Integration</h2>

                <p>
                    BRO360 integrates with Google services to support
                    authentication and document management.
                </p>

                <p><strong>Google Authentication</strong></p>

                <p>
                    When a user signs in using Google, BRO360 may receive
                    basic account information, such as the user's name,
                    email address, and Google account identifier, to
                    authenticate the user and facilitate authorized access.
                </p>

                <p><strong>Google Drive Integration</strong></p>

                <p>
                    BRO360 uses Google Drive integration to create, store,
                    organize, retrieve, and manage documents associated
                    with official tasks and activities. Access is limited
                    to the permissions granted through Google's
                    authorization process.
                </p>

                <p><strong>Google User Data Protection</strong></p>

                <p>
                    Information obtained through Google APIs is used only
                    for the disclosed BRO360 functionality. BRO360 does
                    not sell Google user data or use it for advertising
                    or unrelated purposes.
                </p>

                <p>
                    BRO360's use and transfer of information received from
                    Google APIs will adhere to the
                    <a href="https://developers.google.com/terms/api-services-user-data-policy"
                       target="_blank" rel="noopener noreferrer">
                        Google API Services User Data Policy
                    </a>,
                    including its Limited Use requirements.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">05</span> Data Storage and Retention</h2>

                <p>
                    BRO360 stores application information in its authorized
                    database and uses Google Drive for the storage and
                    management of documents associated with the system.
                </p>

                <p>
                    Information is retained only for as long as necessary
                    to fulfill official, administrative, operational,
                    legal, and records management purposes. Records may
                    be archived or disposed of in accordance with
                    applicable government records management requirements
                    and retention schedules.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">06</span> Disclosure and Sharing of Information</h2>

                <p>
                    Information within BRO360 may be accessed by authorized
                    personnel according to their assigned roles,
                    responsibilities, and access permissions.
                </p>

                <p>
                    Information may be disclosed when necessary for
                    authorized government functions, compliance with
                    applicable laws, lawful government requirements,
                    system maintenance, or protection of legitimate
                    government interests.
                </p>

                <p>
                    BRO360 does not sell, rent, or commercially distribute
                    personal information or Google user data.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">07</span> Data Security</h2>

                <p>
                    BRO360 implements reasonable organizational, physical,
                    and technical safeguards to protect personal information
                    against unauthorized access, alteration, disclosure,
                    loss, destruction, or other unlawful processing.
                </p>

                <ul>
                    <li>User authentication and role-based access controls.</li>
                    <li>Restricted access to official records and documents.</li>
                    <li>Secure connections for accessing the system.</li>
                    <li>System activity monitoring and audit logs.</li>
                    <li>Appropriate backup and recovery procedures.</li>
                    <li>Security measures for the database and connected services.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">08</span> Rights of Data Subjects</h2>

                <p>
                    In accordance with the Data Privacy Act of 2012 and
                    applicable regulations, individuals may exercise
                    their rights as data subjects, subject to legal
                    limitations and applicable government records
                    retention requirements.
                </p>

                <ul>
                    <li>Right to be informed.</li>
                    <li>Right to access personal information.</li>
                    <li>Right to object to certain forms of processing.</li>
                    <li>Right to rectification of inaccurate or incomplete information.</li>
                    <li>Right to erasure or blocking, when legally applicable.</li>
                    <li>Right to data portability, when applicable.</li>
                    <li>Right to lodge a complaint with the National Privacy Commission.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">09</span> Google Permissions and Revocation</h2>

                <p>
                    Users or authorized account holders may review and
                    manage applications connected to their Google
                    Accounts through their Google Account security settings.
                </p>

                <p>
                    Revoking BRO360's Google access may affect Google
                    authentication or document management functionalities.
                    Revocation does not automatically delete official
                    records or documents already stored in BRO360 or
                    Google Drive. Requests concerning such records shall
                    be handled in accordance with applicable government
                    records management and data protection requirements.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">10</span> Cookies and Session Information</h2>

                <p>
                    BRO360 uses session cookies and related technical
                    mechanisms to maintain authenticated sessions,
                    support navigation, protect user accounts, and
                    provide essential system functionality.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">11</span> Changes to This Privacy Policy</h2>

                <p>
                    BRO may update this Privacy Policy when necessary
                    to reflect changes in BRO360 functionality,
                    information processing practices, applicable laws,
                    or government policies. Updated versions shall
                    be published on this page with the corresponding
                    revision date.
                </p>
            </section>

            <section class="policy-section">
                <h2><span class="section-number">12</span> Contact Information</h2>

                <p>
                    For inquiries, concerns, or requests regarding the
                    processing of personal information through BRO360,
                    users may contact:
                </p>

                <div class="contact-card">
                    <p><strong>Bangsamoro Revenue Office (BRO)</strong></p>
                    <p>Ministry of Finance and Budget Management</p>
                    <p>Email: bro@mfbm.bangsamoro.gov.ph</p>
                    <p>Office Address: Bangsamoro Government Center, Rosary Heights 8, Cotabato City, Maguindanao Del Norte</p>
                </div>

                <p style="margin-top: 15px;">
                    Data privacy concerns may also be referred to the
                    <a href="https://privacy.gov.ph/"
                       target="_blank" rel="noopener noreferrer">
                        National Privacy Commission
                    </a>.
                </p>
            </section>

        </div>
    </div>

</main>

<footer class="policy-footer">
    <p><strong>BANGSAMORO REVENUE OFFICE</strong></p>
    <p>Ministry of Finance and Budget Management</p>
    <p class="footer-gold">BRO360 – Task and Program Management System</p>
    <p style="margin-top: 15px;">© 2026 BRO. All rights reserved.</p>
</footer>

</body>
</html>
