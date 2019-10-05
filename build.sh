#!/usr/bin/env bash
base_dir=$(dirname "$0")

server_dir="${base_dir}/"
server_public_dir_name="public"
server_public_dir="${server_dir}/${server_public_dir_name}/"

client_dir="${base_dir}/../spendings-client/"
client_build_dir="${client_dir}/dist/Spendings/"

build_dir="${base_dir}/../build/"
build_public_dir="${build_dir}/htdocs/"

constants_file="${build_dir}init.php"

props_file="production"

if [[ "$1" != "" ]]
then
    props_file=$1
fi

echo "Directory: ${base_dir}"

serversetup(){
    cd ${server_dir}
    composer install
    cd -
}

clientsetup(){
    cd ${client_dir}
    ng build --prod
    cd -
}

build(){
    pwd
    if [[ -d ${build_dir} ]]
    then
        rm -fr $build_dir
        echo "Build directory removed"
    fi

    echo "${build_dir}"
    mkdir $build_dir
    cp -r ${server_dir} ${build_dir}
    rm -fr "${build_dir}${server_public_dir_name}"
    
    echo "${build_public_dir}"
    mkdir $build_public_dir
    cp -r ${server_public_dir} ${build_public_dir}
    cp -r ${client_build_dir} ${build_public_dir}
}

function prop {
    grep "${1}" ${base_dir}/properties/${props_file}.properties|cut -d'=' -f2
}

constants(){
    updateables=$(grep -o '{[a-z.]*}' $constants_file)
    for update in $updateables
    do
        update=$(echo ${update#\{} | rev)
        update=$(echo ${update#\}} | rev)
        update_value=$(prop $update)
        escaped_update_value=$(echo $update_value | sed -e 's/\\/\\\\/g; s/\//\\\//g; s/&/\\\&/g')
        sed -i '' "s/{$update}/$escaped_update_value/g" $constants_file
    done
}

serversetup
clientsetup
build
constants
