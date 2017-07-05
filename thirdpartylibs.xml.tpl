<?xml version="1.0"?>
<libraries>
    <library>
        <location>service</location>
        <name>res_search_service</name>
        <license>Apache v2</license>
    </library>
    {{#libraries}}
    <library>
        <location>{{location}}</location>
        <name>{{name}}</name>
        {{#version}}
        <version>{{version}}</version>
        {{/version}}
        <license>{{license}}</license>
    </library>
    {{/libraries}}
</libraries>
