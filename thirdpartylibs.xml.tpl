<?xml version="1.0"?>
<libraries>
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
