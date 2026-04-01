import bcrypt
from db import init_db, add_user, get_user

def hash_password(password: str) -> bytes:
    return bcrypt.hashpw(password.encode(), bcrypt.gensalt())

def check_password(password: str, hashed: bytes) -> bool:
    return bcrypt.checkpw(password.encode(), hashed)

def register():
    username = input("Enter new username: ")
    password = input("Enter new password: ")
    hashed = hash_password(password)
    add_user(username, hashed)
    print(f"User '{username}' registered successfully!")

def login():
    username = input("Username: ")
    password = input("Password: ")
    user = get_user(username)
    if not user:
        print("User not found.")
        return False
    stored_username, stored_hash = user
    if check_password(password, stored_hash):
        print(f"Welcome back, {stored_username}!")
        return True
    else:
        print("Incorrect password.")
        return False

def main():
    init_db()
    print("Welcome to Tunelec test system.")
    while True:
        print("\nChoose an option:")
        print("1. Register")
        print("2. Login")
        print("3. Exit")
        choice = input("Your choice: ")
        if choice == "1":
            register()
        elif choice == "2":
            if login():
                print("Logged in successfully. You can now run tests...")
                # TODO: Add test running code here
                break
        elif choice == "3":
            print("Goodbye!")
            break
        else:
            print("Invalid choice. Please try again.")

if __name__ == "__main__":
    main()
