# Geniai Base Plugin (Backend)

This repository contains the **core backend logic** for the AAC Moodle Chatbot. All backend processing and integrations must be managed here.

> ⚠️ **Note:** Do not mix frontend or UI activity code with this repository. All UI code should reside in the [moodle-chatbot](https://github.com/jpo5417/moodle-chatbot) repository.

---

## 📥 Installation Guide

Follow these steps to install the plugin into your Moodle environment:

1. **Download** the latest ZIP archive of this repository.
2. **Navigate** to Moodle and proceed to:

   ```
   Site Administration → Plugins → Install Plugins → Install plugin from ZIP file
   ```
3. **Upload** the downloaded ZIP file and complete the installation.

The plugin will be installed at:

```
local/geniai
```

---

## 🔄 Frontend Synchronization

Ensure synchronization between backend updates and frontend UI changes:

* After updating backend logic, ensure any relevant API changes or adjustments are reflected in the frontend chatbot activity plugin.
* **Reminder:** Do not push frontend UI/activity code to this backend repository.

---

## 🚀 Contributing Workflow

To contribute to this project, follow the structured Git workflow:

### 1. Clone Repository

Clone via SSH:

```bash
git clone git@github.com:DrKat0m/Geniai_Base_Plugin.git
cd Geniai_Base_Plugin
```

### 2. Create Feature Branch

Create and switch to your branch:

```bash
git checkout -b your-feature-branch
```

### 3. Commit and Push Changes

Make your changes, then stage, commit, and push:

```bash
git add .
git commit -m "Descriptive commit message"
git push -u origin your-feature-branch
```

### 4. Submit Pull Request

Open a Pull Request on GitHub for review and merging.

---

## 🐞 Debugging with Xdebug

For setting up Xdebug with PHP, refer to Kartavya’s comprehensive guide on Discord:

🔗 [Xdebug Setup Guide](<script src="https://gist.github.com/DrKat0m/3221c4a31b821920085306184c0b290c.js"></script>)

---

## ✨ Fresh Installation & Setup

Follow these instructions for a clean setup:

1. **Ensure Git installation:** [Download Git](https://git-scm.com/downloads)

2. **Navigate to Moodle's local plugins directory:**

```bash
cd D:\xampp\htdocs\moodle\local
```

3. **Clone the repository:**

```bash
git clone git@github.com:DrKat0m/Geniai_Base_Plugin.git
```

4. **Create a working branch:**

```bash
cd Geniai_Base_Plugin
git checkout -b your-feature-branch
```

---

## ✅ Important Guidelines

* Commit **only backend changes** to this repository.
* Update dependent frontend changes in the [moodle-chatbot](https://github.com/jpo5417/moodle-chatbot) repository promptly.
* Do not push chatbot activity module or block code to this repository to prevent codebase clutter and conflicts.
