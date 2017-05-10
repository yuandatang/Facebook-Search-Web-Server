 <html>
    <head>
        <style>
    h1 {
        font-size: 25px;
    }
    #submit_form { 
            border-collapse: collapse;
            border: 1px solid black;
            text-align: center;
            width: 700px;
            margin-left: auto;
            margin-right: auto;
            font-size: 15px;
            background-color:#f4f4f4;
        }
        </style>
    </head>
    <body>
        <?php
            ini_set('session.cache_limiter','public');
            session_cache_limiter(false);
            session_start();
            require_once __DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
            $fb = new Facebook\Facebook([
              'app_id' => '160143257832704',
              'app_secret' => 'b94eb966e3f4e6b20f7381842ffa0f41',
              'default_graph_version' => 'v2.8',
            ]);
        
            $fb->setDefaultAccessToken('EAACRpkHZCDQABAIAPCSlZA7N16qhKTMkjAKD96BCKNNiBOVjxMKYsJ0Jl6fxI3cMwBSontcHmBfvH471ZCsGzgXsT4jNUsJjhcUdVboEz8vKEK6zpSLZAMTbzK4sLeaOrVqZAOu3FxBeQNB61iwraQcnRZCcRLFVYZD');
            
            $search_Str = $keywords = $tab_str = $location = $distance = $user_id = "";
            $type = "Users";
            
            $google_api = "AIzaSyDvaUrUhYkRzZigHfKfre6ijnqNHrRFTQQ";
        
        
            if (!empty($_POST['search_submit'])){
                $keywords = $_POST['keywords'];
                $type = $_POST['type'];
                if ($type == "place") {
                    $location = $_POST['location'];
                    $distance = $_POST['distance'];
                    $contents = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$keywords."&key=".$google_api);
                    $content = json_decode($contents, true);
                    
//                    941+Bloom+Walk,Los+Angeles,
//+CA+90089-0781
                    
                    $lat = $content["results"][0]["geometry"]["location"]["lat"];
                    $lng = $content["results"][0]["geometry"]["location"]["lng"];
                    $search_str = 'search?q='.$keywords.'&type='.$type.'&center='.$lat.','.$lng.'&distance='.$distance.'&fields= id,name,picture.width(700).height(700)';
                }
                else if($type=="event"){
                    $search_str = 'search?q='.$keywords.'&type='.$type.'&fields= id,name,picture.width(700).height(700),place';
                }
                else {
                    $search_str = 'search?q='.$keywords.'&type='.$type.'&fields= id,name,picture.width(700).height(700)';
                    
                    https://graph.facebook.com/v2.8/search?q=The_Keyword_to_be_searched&type=event&fields= id,name,picture.width(700).height(700),place&access_token=Your_Access_Token
                }
                
                try {
                  $response = $fb->get($search_str);
                  $userEdge = $response->getGraphEdge();
                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                  // When Graph returns an error
                  echo 'Graph returned an error: ' . $e->getMessage();
                  exit;
                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                  // When validation fails or other local issues
                  echo 'Facebook SDK returned an error: ' . $e->getMessage();
                  exit;
                }
                    
                if (count($userEdge)==0) {
                    $tab_str = 'No Records have been Found';
                } else {
                    $tab_str = "<table frame = \"border\" rules=\"all\" width=\"70%\" align=\"center\" style=\"text-align:center\">\n";
                    $tab_str.="<tr><td><b>Profile Photo</b></td>
                    <td><b>Name</b></td>";
                    
                    if ($type != "event"){$tab_str.="<td><b>Details</b></td>";}
                    else{$tab_str.="<td><b>Place</b></td>";}
                    $i = 0;
                    
                    
                    foreach ($userEdge as $userNode) {
                        $tab_str.="<tr>\n";
                        $url = $userNode->getProperty('picture')->getProperty('url');
                        $tab_str.="<td><a target='_blank' href ='".$url."'><img src='".$url."' height = '30' width = '40' /></a></td>\n";
                        
                        if($type=="event"){
                            $tab_str.="<td>".$userNode->getProperty('name')."</td>\n";
                            //$tab_str.="<td>".$userNode->getProperty('name')."</td>\n";
                            $tab_str.="<td>".$userNode->getProperty('place')->getProperty('name')."</td>\n";
                        }
                        else{
                            $tab_str.="<td>".$userNode->getProperty('name')."</td>\n";
                            
                            
                            $tab_str.="<td><form id=\"detail".$i."\" method=\"post\" action=\"/search.php\" >\n";
                            $tab_str.= "<a href=\"#\" name=\"detail_link\" value =\"a\" onclick=\"document.getElementById('detail".$i."').submit();\">Details</a>\n";
                            
                            $tab_str.="<input type=\"hidden\" name=\"detail_submit\" value=\"true\">";
                            $tab_str.="<input type=\"hidden\" name=\"user_id\" value=\"".$userNode->getProperty('id')."\">";
                            $tab_str.="<input type=\"hidden\" name=\"keywords\" value=\"".$keywords."\">";
                            $tab_str.="<input type=\"hidden\" name=\"type\" value=\"".$type."\">";
                            $tab_str.="<input type=\"hidden\" name=\"location\" value=\"".$location."\">";
                            $tab_str.="<input type=\"hidden\" name=\"distance\" value=\"".$distance."\">";
                            $tab_str.= "</form></td>"; 
                        }
                        $tab_str.="</tr>\n";
                        $i++;
                    }
                    $tab_str.="</table>\n";   
                }
            }
            
            if (!empty($_POST['detail_submit'])){
                $user_id = $_POST['user_id'];
                $search_str = $user_id."?fields=id,name,picture.width(700).height(700),albums.limit(5){name,photos.limit(2) {name, picture}},posts.limit(5)";
                $keywords = $_POST['keywords'];
                $type = $_POST['type'];
                $location = $_POST['location'];
                $distance = $_POST['distance'];                
                try {
                  $response = $fb->get($search_str);
                  $userNode = $response->getGraphUser();
                } catch(Facebook\Exceptions\FacebookResponseException $e) {
                  // When Graph returns an error
                  echo 'Graph returned an error: ' . $e->getMessage();
                  exit;
                } catch(Facebook\Exceptions\FacebookSDKException $e) {
                  // When validation fails or other local issues
                  echo 'Facebook SDK returned an error: ' . $e->getMessage();
                  exit;
                }
                $albums = $userNode->getProperty("albums");
                $posts = $userNode->getProperty("posts");
                
                if (isset($albums)) {
                    $tab_str = "<div style='width:800px;background-color:lightgray;margin:auto'><h3 align='center'><a href=\"javascript:toggle('album', -1)\"><font color='blue'>Albums</font></a></h3></div>";
                    $j = 0;
                    $tab_str.= "<table frame = \"border\" rules=\"all\" width=\"700px\" align=\"center\" >";
                    foreach ($albums as $album) {
                        $count = 0;
                        $album_pics = $album->getProperty('photos');
                        $tab_str.="<tr name = 'album' class = 'album' hidden><td><a href=\"javascript:toggle('album', '".$j."')\"><font color='blue'>".$album->getProperty('name')."<font></a></td></tr>";
                        $tab_str.="<tr  name = 'album' id = 'album_pic".$j."' hidden><td>";
                        foreach ($album_pics as $pic) {
                            try {
                              $response = $fb->get($pic->getProperty('id')."/picture?&redirect=false");
                              $picNode = $response->getGraphNode();
                            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                              // When Graph returns an error
                              echo 'Graph returned an error: ' . $e->getMessage();
                              exit;
                            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                              // When validation fails or other local issues
                              echo 'Facebook SDK returned an error: ' . $e->getMessage();
                              exit;
                            }
                            $origin_url = $picNode->getProperty('url');
                            $pic_url = $pic->getProperty('picture');
                            if ($count >= 2) break;
                            $tab_str.= "<a target='_blank' href ='".$origin_url."' ><img src='".$pic_url."'/></a>";
                            $count++;
                        }
                        $tab_str.="</td></tr>";
                        $j++;
                    }
                    $tab_str.= "</table>";
                } else {
                    $tab_str = "<h3 align='center'>No Albums has been found</h3>";
                }
                
                
                if (isset($posts)) {
                    $tab_str.= "<table frame = \"border\" rules=\"all\" width=\"70%\" align=\"center\" >";
                    
                    $tab_str.= "<div style='width:800px;background-color:lightgray;margin:auto'><h3 align='center'><a href=\"javascript:toggle('post', -1)\"><font color='blue'>Posts</font></a></h3><div>";
                    $tab_str.= "<tr class = 'post' hidden><td><b>Message</b></td></tr>";
                    foreach ($posts as $post) {
                        $tab_str.= "<tr class = 'post' hidden><td>".$post->getProperty('message')."</td></tr>";
                    }
                    $tab_str.= "</table>";
                } else {
                    $tab_str.= "<h3 align='center'>No Posts has been found</h3>";
                }
            }
        ?>
        
            <form align="center" id = "submit_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" onclick = "return check();"> 
                <h1><u width=1500px>Facebook Search</u></h1>
                <table>
                    <tr>
                        <td >Keywords</td>
                        <td>
                            <input type="text" name="keywords" id = "keyword" value = "<?php echo $keywords;?>" required><br>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>Type</td>
                        <td>
                              <select name="type" id="selectBox" onchange="replace();">
                              <option value="user" <?php if ($type == "user") echo "selected"?>>Users</option>
                              <option value="page" <?php if (isset($type)&& $type== "page") echo "selected"?>>Pages</option>
                              <option value="event" <?php if (isset($type)&& $type== "event") echo "selected"?>>Events</option>
                              <option value="group" <?php if (isset($type)&& $type== "group") echo "selected"?>>Groups</option>
                              <option value="place" <?php if (isset($type)&& $type== "place") echo "selected"?>>Places</option>
                              </select>
                        </td>
                    </tr>
                    
                    <tr id = "Position" <?php if(isset($type)&& $type!= "place")echo "hidden"?>>
                        <td>Location</td>
                        <td>
                            <input type="text" name = "location" id = "location" value = "<?php echo $location;?>">
                        </td>
                        <td>Distance(meters)</td>
                        <td>
                            <input type="text" name = "distance" id = "distance" value = "<?php echo $distance;?>">
                        </td>
                    </tr>
                    
                    
<!--
                    <tr id = "Position2"<?php if(isset($type)&& $type != "place")echo "hidden"?>>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
-->
                    
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" name="search_submit" value="Search">
                            <input type ="button" value = "Clear" onclick = "clearform();"><br>
                        </td>
                    </tr>
                </table>
            </form>
        <div style="margin-top:30px" id = "result_form">
            <?php echo $tab_str?>
        </div>
        
        <script type="text/javascript">
            //show location and distacne
            function replace(){
                
                var changed = "";
                var selectBox = document.getElementById("selectBox");
                var key = selectBox.options[selectBox.selectedIndex].value;
                if (key == "place") {
                    document.getElementById("Position").removeAttribute('hidden');
                    //document.getElementById("Position2").setAttribute('hidden',true);
                } else {
                    document.getElementById("Position").setAttribute("hidden",true);
                    //document.getElementById("Position2").removeAttribute('hidden');
                }
            }
            // clear function
            function clearform(){
                document.getElementsByName("type")[0].selectedIndex = 0;
                document.getElementById("keyword").value = "";
                document.getElementById("Position").setAttribute("hidden",true);
                document.getElementById("result_form").innerHTML = "";
            }
            
            function toggle(attr,index) {
                if (attr == 'album') {
                    if (index == -1) {
                        var content = document.getElementsByClassName('album');
                        if (content[0].hasAttribute('hidden')) {
                            for (var i = 0; i < content.length; ++i) {
                                content[i].removeAttribute('hidden');
                            }
                            var content1 = document.getElementsByClassName('post');
                            if (!content1[0].hasAttribute('hidden')) {
                                for (var i = 0; i < content1.length; ++i) {
                                    content1[i].setAttribute('hidden', true);
                                }
                            }
                        } else {
                            for (var i = 0; i < content.length; ++i) {
                                content[i].setAttribute('hidden', true);
                            }
                        }
                    
                    } else {
                        var content = document.getElementById('album_pic'+index.toString());
                        if (content.hasAttribute('hidden')) {
                            content.removeAttribute('hidden');
                        } else {
                            content.setAttribute('hidden', true);
                        }                        
                    }
                } else {
                    var content = document.getElementsByClassName('post');
                    if (content[0].hasAttribute('hidden')) {
                        for (var i = 0; i < content.length; ++i) {
                            content[i].removeAttribute('hidden');
                        }
                        var content1 = document.getElementsByName('album');
                        if (!content1[0].hasAttribute('hidden')) {
                            for (var i = 0; i < content1.length; ++i) {
                                content1[i].setAttribute('hidden', true);
                            }
                        }
                    } else {
                        for (var i = 0; i < content.length; ++i) {
                            content[i].setAttribute('hidden', true);
                        }
                    }
                    
                }
            }
        </script>
    </body>
</html>