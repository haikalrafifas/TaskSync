# About
---

TaskSync is an application to help students to manage their tasks and homeworks. This application relies on school/university website that uses Moodle-based services. Users are required to make their account on their institution website before using this application.

> As of version `v1.0.0`, this applicatino only supports on Windows OS

# How to Use
---

1. Download this app on zip, or git clone
2. Open your command prompt and change the directory to this app's working directory. For example, if the source code is on the Downloads folder, you can enter the command below:
   ```
   cd /d C:/Users/your-username/Downloads/tasksync
   ```
3. Edit the `package.json` file on block `default.api.domain` and set the value to your institution's website domain
4. Navigate to application binaries:
   ```
   cd system/bin
   ```
5. Start the application using the command below:
   ```
   app start
   ```
6. Open your browser and go to `http://localhost`
7. The application is ready to use. To stop the application, open your command prompt and enter the command below:
   ```
   app stop
   ```

# Extras
---

Created at 2023-05-27 by haikalrafifas@github.com