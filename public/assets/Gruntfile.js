module.exports = function (grunt) {
    "use strict";

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            all: {
                options: {
                    unixNewlines: true,
                    compass: true,
                    lineNumbers: false
                },
                files: {
                    'css/main.css': 'scss/main.scss'
                }
            },
        },
        watch: {
            sass: {
                files: [
                    'scss/**/*.scss'
                ],
                tasks: ['sass'],
            }
        },
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');

    grunt.registerTask("default", ["sass"]);
};
