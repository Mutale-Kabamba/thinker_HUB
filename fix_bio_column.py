import sqlite3
import sys

db_path = "database/database.sqlite"
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Check if bio column exists
cursor.execute("PRAGMA table_info(users)")
columns = [col[1] for col in cursor.fetchall()]

if "bio" in columns:
    print("bio column already exists")
    sys.exit(0)

# Add bio column
cursor.execute("ALTER TABLE users ADD COLUMN bio TEXT")
conn.commit()
print("bio column added successfully")
