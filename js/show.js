
        function showId2(){
            var userId =$("#uId2").val();
            if(userId !=""){
                $('#idmsg2').show();
                $('#idmsg2').html("<font color='#ff0000'>"+ userId +"</font>");
            }else{
                $('#idmsg2').hide();
            }
        } 
        function showId()
        {
            //let可取代 var
            var userId = document.getElementById("uId").value;
            //if (userId !="")不等於空值
            if (userId !=""){
                /*
                inline 同一行;block 換行
                document.getElementById("idmsg").sytle.display ="inline"
                */
                document.getElementById("idmsg").style.display = "block";
                document.getElementById("idmsg").innerHTML = "<font color = 'red'>" + userId + "</font>";
            }else{
            //身分證沒有輸入資料時，將id=idmsg隱藏
                document.getElementById("idmsg").style.display = "none";
            }
        }
