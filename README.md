```markdown
# Geniai Base Plugin (Backend)

This repository contains the **core backend logic** for the AAC Moodle Chatbot.  
All backend processing and integrations should be done and maintained here.

> ⚠️ **Do not mix up frontend/activity code with this repository.**  
> All chatbot activity UI code should go in the [moodle-chatbot](https://github.com/DrKat0m/moodle-chatbot) repo.

---

## 🔧 Installing the Plugin

1. Download the ZIP of the latest version from this repository.
2. In Moodle, go to:  
   `Site Administration → Plugins → Install Plugins → Install plugin from the ZIP file`
3. Upload and install the plugin.

This plugin should install into the `local/geniai` directory inside your Moodle codebase.

---

## 🔄 Keeping Frontend (Activity Plugin) in Sync

- After updating backend logic here, ensure that relevant API changes or logic adjustments are reflected in the chatbot activity plugin as well.
- **Do not push UI/activity code into this repo.**

---

## 🛠️ Development Workflow

If you're planning to contribute, here's a recommended Git workflow:

### 1. Clone the repository via SSH

```bash
git clone git@github.com:DrKat0m/Geniai_Base_Plugin.git
cd Geniai_Base_Plugin
```

### 2. Create a new branch

```bash
git checkout -b your-branch-name
```

### 3. Make your changes  
Then stage, commit, and push:

```bash
git add .
git commit -m "Your descriptive commit message"
git push -u origin your-branch-name
```

### 4. Open a pull request  
Do this on GitHub so your changes can be reviewed.

---

## 🧪 Debugging with Xdebug

If you’re setting up Xdebug with PHP, follow the detailed step-by-step guide by Kartavya in the Discord Resource Channel:  
🔗 [Discord Guide](https://discord.com/channels/1328761463774511164/1328771210087235595/1346614587008618588)

---

## 🧹 Clean Cloning Guide (For Fresh Setups)

1. Make sure Git is installed:  
   👉 [https://git-scm.com/downloads](https://git-scm.com/downloads)

2. Navigate to where you want to install the plugin:
```bash
cd D:\xampp\htdocs\moodle\local
```

3. Clone the repository:
```bash
git clone git@github.com:DrKat0m/Geniai_Base_Plugin.git
```

4. Create and switch to a new branch for your work:
```bash
cd Geniai_Base_Plugin
git checkout -b your-branch-name
```

---

## ✅ Important Notes

- Always commit backend changes to this repository.
- Keep the [moodle-chatbot](https://github.com/DrKat0m/moodle-chatbot) repo updated with any dependent frontend changes.
- Avoid pushing any chatbot activity code (block/activity module) to this repo to prevent merge conflicts and code pollution.

---
```