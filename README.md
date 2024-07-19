# SimplicityAPIV2

Token API
Comandos de la API: 

Readd IP

    URL: ?ip={ip}
    Descripción: Lee la dirección IP especificada. Si está en la lista negra, marca "NO" bajo "Readded" y "YES" bajo "Ban". Si no está en la lista negra, la agrega a la lista de IP y marca "NO" bajo "Readded" y "NO" bajo "Ban".

Add to Blacklist

    URL: ?addb
    Descripción: Agrega la dirección IP del cliente actual a la lista negra y devuelve un estado 200 OK.

Clear Files

    URL: ?clear
    Descripción: Limpia todos los archivos relevantes y directorios asociados (como ips.txt, blacklist.txt, active.txt, inactive.txt y archivos en ./2fa), devolviendo un estado indicando que los archivos se han borrado.

Get Status

    URL: ?status
    Descripción: Devuelve un estado 200 OK indicando que la solicitud fue exitosa.

Edit User Data

    URL: ?user={token}&{field}={value}
    Descripción: Edita los campos especificados (Status, Log, card, mail, data1, data2, data3, par1, par2, par3) en el archivo JSON asociado al token proporcionado. Si el usuario no existe, devuelve un estado indicando que no se encontró el usuario.

Get User Data

    URL: ?3d={token}
    Descripción: Obtiene los datos asociados al token especificado en formato JSON. Si el token no existe, devuelve un estado indicando que no se encontró el token.


/Delivr        

    ?loadtokens=! - Mueve tokens desde ok.txt a okload.txt.
        Ejemplo de uso: ?loadtokens=!

    ?newtoken=! - Carga un token desde okload.txt, muestra el token y lo mueve a delivered.txt.
        Ejemplo de uso: ?newtoken=!


/users API

Borrar archivo JSON: ?clr={nombre_archivo}

    Borra el archivo JSON especificado en la carpeta /2fa.

Limpiar carpeta JSON: ?3d=clear

    Borra todos los archivos JSON en la carpeta /2fa.

Estado de la API: ?status=!

    Retorna un mensaje de éxito y estado 200 OK.

    
ADMIN
    administrator3000
    FullApis2012!!!


Editar archivo JSON: ?3d={token}&{parámetros}

    Edita los campos específicos (par1, par2, etc.) del archivo JSON correspondiente al {token} en la carpeta /2fa.

