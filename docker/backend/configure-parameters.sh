#!/bin/bash
# configure-parameters.sh

# configure default postgres connection
sed -i s/\$database_host/$POSTGRES_DB_HOST/ /backend/app/config/parameters.yml
sed -i s/\$database_port/$POSTGRES_DB_PORT/ /backend/app/config/parameters.yml
sed -i s/\$database_name/$POSTGRES_DB_NAME/ /backend/app/config/parameters.yml
sed -i s/\$database_user/$POSTGRES_DB_USER/ /backend/app/config/parameters.yml
sed -i s/\$database_password/$POSTGRES_DB_PASSWORD/ /backend/app/config/parameters.yml

# configure test postgres connection
sed -i s/\$database_test_host/$POSTGRES_DB_TEST_HOST/ /backend/app/config/parameters.yml
sed -i s/\$database_test_port/$POSTGRES_DB_TEST_PORT/ /backend/app/config/parameters.yml
sed -i s/\$database_test_name/$POSTGRES_DB_TEST_NAME/ /backend/app/config/parameters.yml
sed -i s/\$database_test_user/$POSTGRES_DB_TEST_USER/ /backend/app/config/parameters.yml
sed -i s/\$database_test_password/$POSTGRES_DB_TEST_PASSWORD/ /backend/app/config/parameters.yml

# configure comet connection
sed -i s/\$comet/$COMET/ /backend/app/config/parameters.yml
