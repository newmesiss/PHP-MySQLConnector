# PHP MySQLConnector
>Yet another php class to manage MySQL database servers conections.


- Autor: SDogo WildDog (Moises Rodriguez P.)
- Lenguaje: PHP
- Uso: Conexiones a bases de datos MySQL.
- Estilo codificacion: Notacion Hungara + CamelCase


#####(ReadME v0.1)
##[Español]
>Una clase diseñada para conectarse a servidores MySQL bajo PHP5.

Caracteristicas:
- Programacion POO
- Soporte para querys simples
- Soporte para prepared statements
- Formato de resultado seleccionable (array o json)

Pendientes:
- Limpieza general del codigo
- Agregar mas validaciones por tipo
- Crear una documentacion real de la clase

Requerimientos
- Servidor PHP5 (Puede ser en modo Fast-CGI incluso (Nginx, Lighttpd) )
- Servidor MySQL (Testeado bajo version 5.5.27)
- Extension mysqli disponible en PHP (Excluyente).

Disclamer:
La clase es un WIP hasta el momento y esta siempre en constante mejora. Su uso en ambientes de produccion queda a criterio de cada uno ya que no se garantiza la compatibilidad entre distintas versiones.