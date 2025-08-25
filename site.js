function copy(id){
  let href = document.getElementById("href" + id.toString());
  let contentDiv = document.getElementById("originalclip_" + id.toString());
  let clipContent = "";
  if(href) {
    clipContent = href.textContent;
  } else if(contentDiv) {
    clipContent = contentDiv.textContent;
	
  }
	//clipContent = clipContent.replace(/&gt;/g, '>').replace(/&lt;/g, '<'); 
  if(clipContent) {
    copyHack(clipContent);
    var backgroundColor = document.body.style.backgroundColor;
    document.body.style.backgroundColor = '#ccffcc';
    setTimeout(function () {document.body.style.backgroundColor = backgroundColor}, 200);
  }
}

function copyHack(value){ //such a hack!!
    let copyTextarea = document.createElement("textarea");
    copyTextarea.style.position = "fixed";
    copyTextarea.style.opacity = "0";
    copyTextarea.textContent = value;
 
    document.body.appendChild(copyTextarea);
    copyTextarea.select();
    document.execCommand("copy");
    document.body.removeChild(copyTextarea);
}

function gotoSelectedClipType(){
  if(document.getElementById("clip") && document.getElementById("clip").value.trim() == "") {
    let select = document.getElementById("clipboard_item_type_id");
    window.location = "?type_id=" + select[select.selectedIndex].value;
  }
}


function reply(otherUserid, clipboardEntryId) {
  const userDropdown = document.getElementById("other_user_id");
  const parentClipBoardItemIdInput = document.getElementById("parent_clipboard_item_id");
  if(parentClipBoardItemIdInput) {
    parentClipBoardItemIdInput.value = clipboardEntryId;
  }
  for (let i = 0; i < userDropdown.options.length; i++) {
  
    const option = userDropdown.options[i];
    console.log(otherUserid, option.value);
    if (parseInt(option.value) === parseInt(otherUserid)) {
      option.selected = true;
      break;
    }
  }


}

function clipTypeDropdown(data, defaultValue, onChange, jsId) {
  return genericSelect(jsId, jsId, defaultValue, data, "onchange", onChange);

}

function genericSelect(id, name, defaultValue, data, event = "", onChange = "") {
  let out = "";
  if (data && data.length) {
    out += `<select name="${name}" id="${id}" ${event}="${onChange}">\n`;
    out += "<option/>"; // empty option at the top

    
      for (let datum of data) {
        // handle associative array/object with "value" and "text"
        let value = (datum && datum.value !== undefined) ? datum.value : "";
        let text = (datum && datum.text !== undefined) ? datum.text : "";

        // if it's just a list of scalars or empty object
        if ((value === "" && text === "") || (!text && typeof datum !== "object")) {
          value = datum;
          text = datum;
        }

        let selected = (defaultValue == value) ? " selected='true'" : "";
        out += `<option${selected} value="${value}">${text}</option>`;
      }
    

    out += "</select>";
  }
  return out;
}


function clipTools(clipId) {
  let out = "";
  out += `<a href="javascript:copy(${clipId})">
            <img src="copy.png" height="10" border="0"/>
          </a>\n`;
  return out;
}



function clips(lastPkValue) {
    let select = document.getElementById("clipboard_item_type_id");
    clearThread = false;
    typeId = '';
    if(select) {
      typeId = select[select.selectedIndex].value;
    }
    if(typeId != oldType) {
      lastPkValue = 0;
      oldType = typeId;
      clearThread = true;
    }
      const payload = {
        action: "expandinglist",
        mode: "json",
        value: typeId,
        column: "type_id",
        table: "clipboard_item",
        pk: "clipboard_item_id",
        clipboard_item_id: lastPkValue,
        limit: 100,
        //pre_entity: table + "+" + pkName + "+" + pkValue,
        hashed_entities: "lol"
      };
      
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            // If your PHP returns JSON:
            const raw = xhr.responseText;
            const data =  JSON.parse(raw);
            const clipboardItems =  data.clips;
            const clipTypes = data.clipTypes;
            let out = "";
            for(let row of clipboardItems){
              console.log(row);
              let clipboardItemId = row["clipboard_item_id"];
              if(globalLastClipboardItemId < clipboardItemId) {
                globalLastClipboardItemId = clipboardItemId;
              }
              
              
              
              
              let userId = row["our_user_id"];

              // assumes you already have rows, rowCount, userId, encryptionPassword
              let provideReply = false;
            
              

              out += "<div class='postRow'>\n<div class='postDate'>" + row.clip_created;

              if (row.other_user_id === userId) {
                out += "<br/>From: " + row.author_email;
                provideReply = true;
              } else if (row.other_user_id > 0) {
                out += "<br/>To: " + row.other_email;
              }

              out += "</div>\n";

              let clip = row.clip;
              let table = "clipboard_item";
              let pk = table + "_id";

      
              let hashedEntities = row.hashed_entities;
              //clipTypeDropdown(data, defaultValue, onChange, jsId)
              if(interface != "app") {
                out += clipTypeDropdown(
                  clipTypes,
                  row.type_id,
                  `changeClipType(${row.clipboard_item_id},'${hashedEntities}','type_${row.clipboard_item_id}')`,
                  `type_${row.clipboard_item_id}`
                );
                }

              if (clip !== "") {
                out += "<div class='clipTools'>" + clipTools(row.clipboard_item_id) + "</div>\n";
              }

              out += "<div class='postClip'>\n";
              out += `<span id='clip${row.clipboard_item_id}'>`;

              let endClip = "";
              if (clip.startsWith( "http")) {
              //if (beginsWith(clip, "http")) {
                out += `<a id='href${row.clipboard_item_id}' href='${clip}'>`;
                endClip = "</a>";
              } else {
                out += "<tt>";
                endClip = "</tt>";
              }

              out += clip.replace(/\n/g, "\n<br/>");

              if (provideReply) {
                out += `<br/><button onclick='reply(${row.author_id},${row.clipboard_item_id})'>reply</button>`;
              }

              out += endClip;
              out += "</span>";

              out += `<span style='display:none' id='originalclip_${row.clipboard_item_id}'>`;
              out += clip;
              out += "</span>";

              if (row.file_name && row.file_name !== "") {
                let extension = row.file_name.split(".").pop();
                let clickUrl = "index.php?friendly=" + encodeURIComponent(row.file_name) + "&mode=download&path=" + encodeURIComponent("./downloads/" + row.clipboard_item_id + "." + extension);
                let lcExtension = extension.toLowerCase();
                representationOfFile = row.file_name;
                if (lcExtension == "jpg" || lcExtension == "png" || lcExtension == "gif") {
                  let filePath = "downloads/" + row.clipboard_item_id + "." + extension ;
                  representationOfFile = "<img width='200' src='" + filePath + "'/>";
                  clickUrl = filePath;
                  out += "<div class='downloadLink'><a href='javascript:showImageOverlay(\"" + filePath + "\")'>" + representationOfFile  + `</a></div>`
                } else {
                  out += "<div class='downloadLink'><a href='" + clickUrl + "'>" + representationOfFile  + `</a></div>`;
                }
              }

              out += "</div>";
              out += "</div>\n";
              
              
              
              
              
              
            }
            let clipsPlace = document.getElementById("clips");
            if(clearThread) {
              clipsPlace.innerHTML = "";
            }
            if(out) {
              clipsPlace.innerHTML = out + clipsPlace.innerHTML;
            }
            out ="";
      
            //window.location.reload();
            //no action necessary
          } else {
            console.error("Request failed:", xhr.status, xhr.statusText);
          }
          setTimeout(()=>{
            clips(globalLastClipboardItemId)
          }, 2000);
        }
      };
      xhr.open("POST", "index.php", true);
      // Tell the server we?re sending JSON
      xhr.setRequestHeader("Content-Type", "application/json");
      xhr.send(JSON.stringify(payload));


}
// Open overlay (example)
function showImageOverlay(imgSrc) {
  const overlay = document.createElement("div");
  overlay.className = "overlay";

  const img = document.createElement("img");
  img.src = imgSrc;

  const btn = document.createElement("button");
  btn.innerText = "X"; // nice unicode X
  btn.onclick = () => overlay.remove();

  // close if backdrop clicked (but not the image)
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay || e.target === overlay.querySelector("::before")) {
      overlay.remove();
    }
  });

  overlay.appendChild(img);
  overlay.appendChild(btn);
  document.body.appendChild(overlay);
}


function changeClipType(clipboardItemId, hashedEntities, jsId) {
  console.log(jsId);
  //return;
    let select = document.getElementById(jsId);
    let value = select[select.selectedIndex].value;
      const payload = {
        action: "update",
        mode: "crud",
        value: value,
        column: "type_id",
        table: "clipboard_item",
        pk: "clipboard_item_id",
        clipboard_item_id: clipboardItemId,
        //pre_entity: table + "+" + pkName + "+" + pkValue,
        hashed_entities: hashedEntities
      };
      
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            // If your PHP returns JSON:
            // const data = JSON.parse(xhr.responseText);
            //console.log("Server replied:", xhr.responseText);
            //window.location.reload();
            //no action necessary
            let outValue = xhr.responseText;
            //NOT SURE WHY I HAVE TO DO THIS!!
            //alert(outValue);
     
            for (let i = 0; i < select.options.length; i++) {
              const option = select.options[i];
              
              if (parseInt(option.value) === parseInt(outValue)) {
                console.log(option);
                option.selected = true;
      
              } else {
                option.selected = false;
              }
            }

          } else {
            console.error("Request failed:", xhr.status, xhr.statusText);
          }
        }
      };
      xhr.open("POST", "index.php", true);
      // Tell the server we?re sending JSON
      xhr.setRequestHeader("Content-Type", "application/json");
      xhr.send(JSON.stringify(payload));
    
}

let oldType = 0;
let globalLastClipboardItemId = 0;
clips(globalLastClipboardItemId);
 
