![page-login](https://user-images.githubusercontent.com/8649070/42580602-9e3bd2b0-8533-11e8-9a37-4ebb02765559.jpg)



travel_agency_crm

=========
Symfony Powerful Dashboard & Admin CRM. Developed with **Symfony 4 Flex** framework.

No changes were made to the symfony structure, the current directory structure is used. A custom namespace for Admin has been created. This field is used for all administrator operations. 

The interface is designed to be responsive using Twitter Bootstrap. The least possible dependency was tried to be used. 

Installation
--------------------
1. Download 
    ```
    composer INSTALL 
    ```
2. Create and configure the `.env` file.

3. Create database schemas
    ```
    bin/console doctrine:schema:create --force
    ```
4. Run built-in web server
     ```
     bin/console server:start
     ```
5. Install & Build assets
     ```
     yarn install
     yarn run build
     ```


```
