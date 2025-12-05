BIP! Services
=============

The suite **BIP! Services** is a suite of services designed to support researchers and other stakeholders with scientific knowledge discovery, research assessment, and other use cases related to their everyday routines.  
The suite comprises four services — **Finder**, **Scholar**, **Readings**, and **Spaces** — each offering unique functionalities addressed to professionals conducting research across all disciplines and to other stakeholders such as funders, research managers, and technologists.  
For more information regarding BIP! Services visit the public site at https://bip.imsi.athenarc.gr/

## Overview of the services

- **Finder**: Search and exploration interface for scholarly works, powered by citation-based indicators and rich filtering.
- **Scholar**: Researcher-oriented views and profiles, focused on summarizing and communicating individual research outputs using the same indicators and concepts as Finder.
- **Readings**: Personalized reading/bookmarking environment for managing papers, reading status, and tags, integrated with the core impact indicators.
- **Spaces**: Configurable, space-specific instances (e.g. pilots, domains, projects) that control defaults, filters, theming, and enable features such as like/dislike records.

## Technology and structure

This repository contains a [Yii 2](https://www.yiiframework.com/)–based PHP web application with:

- `config/` – application configuration (DB, params, URL rules, etc.)
- `controllers/` – controllers for **Finder**, **Scholar**, **Readings**, **Spaces**, and shared pages
- `models/` – ActiveRecord models (e.g. `Article`, `Spaces`, `LikeDislikeRecords`, user-related models)
- `views/` – view templates for the different services (e.g. `views/site/index.php`, `views/scholar/*`, `views/readings/*`)
- `components/` – reusable widgets and helpers (e.g. `ResultItem`, `BookmarkIcon`)
- `web/` – entry script (`index.php`), assets, JS, and CSS

## How to cite

We kindly ask that any published research using **BIP! Finder**, **BIP! Scholar**, **BIP! DB**, or **BIP! NDR** cites the corresponding papers listed below:

- **BIP! Finder**  
  T. Vergoulis, S. Chatzopoulos, I. Kanellos, P. Deligiannis, C. Tryfonopoulos, T. Dalamagas:  
  *BIP! Finder: Facilitating scientific literature search by exploiting impact-based ranking.*  
  Proceedings of the 28th ACM International Conference on Information and Knowledge Management (CIKM), 2019

- **BIP! Scholar**  
  T. Vergoulis, S. Chatzopoulos, K. Vichos, I. Kanellos, A. Mannocci, N. Manola, P. Manghi:  
  *BIP! Scholar: A Service to Facilitate Fair Researcher Assessment.*  
  Joint Conference on Digital Libraries (JCDL), 2022

- **BIP! DB**  
  T. Vergoulis, I. Kanellos, C. Atzori, A. Mannocci, S. Chatzopoulos, S. La Bruzzo, N. Manola, P. Manghi:  
  *BIP! DB: A Dataset of Impact Measures for Scientific Publications.*  
  International Workshop on Scientific Knowledge: Representation, Discovery, and Assessment (Sci-K) @ The Web Conf, 2021

- **BIP! NDR**  
  P. Koloveas, S. Chatzopoulos, C. Tryfonopoulos, T. Vergoulis:  
  *BIP! NDR (NoDoiRefs): A Dataset of Citations From Papers Without DOIs in Computer Science Conferences and Workshops.*  
  International Conference on Theory and Practice of Digital Libraries (TPDL), 2023

Thank you for supporting these tools and datasets by acknowledging their respective publications.

