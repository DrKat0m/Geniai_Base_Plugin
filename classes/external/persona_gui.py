import tkinter as tk
from tkinter import messagebox, simpledialog, ttk
import json
import os

FILENAME = "./classes/external/personas.json"

# Load existing personas
def load_personas():
    if not os.path.exists(FILENAME):
        return {}
    with open(FILENAME, "r") as f:
        return json.load(f)

# Save personas to file
def save_personas(personas):
    with open(FILENAME, "w") as f:
        json.dump(personas, f, indent=2)

# Add new persona
def add_persona():
    key = simpledialog.askstring("Scenario Key", "Enter a unique scenario key (e.g., 'anna'):")
    if not key or key in personas:
        messagebox.showerror("Error", "Invalid or duplicate key.")
        return

    name = simpledialog.askstring("Name", "Enter the persona's full name:")
    description = simpledialog.askstring("Description", "Enter a short persona description:")
    tone = simpledialog.askstring("Tone", "Enter the persona's tone (e.g., worried, confused):")

    lines = []
    for i in range(1, 5):
        line = simpledialog.askstring(f"Line {i}", f"Enter example line {i}:")
        lines.append(line)

    personas[key] = {
        "name": name,
        "description": description,
        "lines": lines,
        "tone": tone
    }

    save_personas(personas)
    print(f"Saving to: {os.path.abspath(FILENAME)}")
    refresh_list()
    messagebox.showinfo("Success", f"Persona '{key}' added.")

# Delete selected persona
def delete_persona():
    selection = listbox.curselection()
    if not selection:
        messagebox.showerror("Error", "No persona selected.")
        return

    key = listbox.get(selection[0])
    if messagebox.askyesno("Confirm", f"Are you sure you want to delete '{key}'?"):
        del personas[key]
        save_personas(personas)
        refresh_list()

# Refresh the listbox
def refresh_list():
    listbox.delete(0, tk.END)
    for key in sorted(personas.keys()):
        listbox.insert(tk.END, key)

# Initialize GUI
personas = load_personas()
root = tk.Tk()
root.title("Persona Manager")

frame = tk.Frame(root)
frame.pack(padx=20, pady=20)

listbox = tk.Listbox(frame, width=40)
listbox.pack()

btn_frame = tk.Frame(frame)
btn_frame.pack(pady=10)

tk.Button(btn_frame, text="Add Persona", command=add_persona).grid(row=0, column=0, padx=5)
tk.Button(btn_frame, text="Delete Persona", command=delete_persona).grid(row=0, column=1, padx=5)

refresh_list()
root.mainloop()
